<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    /**
     * Shared password-strength rule for every place a user sets or
     * changes a password (register, reset, profile update). Requires at
     * least 8 characters with a mix of uppercase, lowercase, a number,
     * and a special character — paired everywhere with the live
     * partials/password_strength checklist so the requirement is
     * visible while typing, not just as an error after submitting.
     */
    public const PASSWORD_RULE = 'min_length[8]|regex_match[/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[^a-zA-Z0-9]).+$/]';
    public const PASSWORD_HINT = 'At least 8 characters, with uppercase, lowercase, a number, and a special character.';

    protected $table            = 'users';
    protected $primaryKey       = 'user_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $allowedFields    = [
        'name', 'last_name', 'first_name', 'middle_name', 'email', 'password',
        'avatar', 'contact_number', 'location', 'street', 'sitio', 'barangay',
        'city_municipality', 'province', 'customer_type', 'customer_type_other',
        'terms_accepted_at', 'role',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // 'name' is intentionally not validated here — it's never set
    // directly by a caller, only auto-computed by composeName() below
    // from last_name/first_name/middle_name (each validated at the
    // controller level, same place the old single 'name' field used to
    // be validated).
    protected $validationRules = [
        'email' => 'required|valid_email|max_length[150]|is_unique[users.email,user_id,{user_id}]',
        // Never itself submitted as data (user_id is the primary key, passed
        // separately to update()) — this rule exists only so the {user_id}
        // placeholder above can resolve when a caller adds 'user_id' to the
        // save data specifically to make that exclusion work. CI4 requires
        // a placeholder field to have declared rules or it throws.
        'user_id' => 'permit_empty',
    ];

    protected $beforeInsert = ['composeName', 'composeLocation'];
    protected $beforeUpdate = ['composeName', 'composeLocation'];

    public function findByEmail(string $email): ?array
    {
        return $this->where('email', $email)->first();
    }

    /**
     * Customers who registered in the last 48 hours — surfaced in the
     * owner topbar's notification dropdown so new sign-ups don't go
     * unnoticed the same way a new reservation wouldn't.
     */
    public function recentCustomerSignupCount(): int
    {
        return $this->where('role', 'customer')
            ->where('created_at >=', date('Y-m-d H:i:s', strtotime('-48 hours')))
            ->countAllResults();
    }

    /**
     * The reservations/payments/sales tables all require a real user
     * account (no nullable customer). Walk-in (over-the-counter) sales
     * ride on this one shared placeholder account instead of forcing a
     * schema change — created once, on first use, then reused forever.
     */
    public function getOrCreateWalkInCustomer(): array
    {
        $existing = $this->findByEmail('walkin@grahamgo.local');
        if ($existing) {
            return $existing;
        }

        $id = $this->insert([
            'last_name'     => 'Customer',
            'first_name'    => 'Walk-in',
            'email'         => 'walkin@grahamgo.local',
            'password'      => password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT),
            'customer_type' => 'Other',
            'customer_type_other' => 'Walk-in',
            'role'          => 'customer',
        ], true);

        return $this->find($id);
    }

    /**
     * Keeps `name` (used everywhere — sessions, emails, reservation/sales
     * listings, reports) in sync with the structured last_name /
     * first_name / middle_name fields, so those callers never had to
     * change even though the actual source of truth is now composite.
     * Only recomputes when at least one of the name parts is actually
     * present in this save (e.g. a password-only update leaves it alone).
     */
    protected function composeName(array $data): array
    {
        if (! isset($data['data'])) {
            return $data;
        }

        $fields = $data['data'];
        if (! array_key_exists('last_name', $fields) && ! array_key_exists('first_name', $fields) && ! array_key_exists('middle_name', $fields)) {
            return $data;
        }

        $existing = [];
        if (! empty($data['id'])) {
            $id = is_array($data['id']) ? reset($data['id']) : $data['id'];
            $existing = $this->find($id) ?? [];
        }

        $first  = $fields['first_name']  ?? ($existing['first_name']  ?? '');
        $middle = $fields['middle_name'] ?? ($existing['middle_name'] ?? '');
        $last   = $fields['last_name']   ?? ($existing['last_name']   ?? '');

        $data['data']['name'] = trim(preg_replace('/\s+/', ' ', "{$first} {$middle} {$last}"));

        return $data;
    }

    /**
     * Same idea as composeName(), but for the address — keeps `location`
     * (the single display string used anywhere an address is shown, e.g.
     * the owner's customer-detail view) in sync with the structured
     * street/barangay/city_municipality/province fields.
     */
    protected function composeLocation(array $data): array
    {
        if (! isset($data['data'])) {
            return $data;
        }

        $fields    = $data['data'];
        $locationKeys = ['street', 'sitio', 'barangay', 'city_municipality', 'province'];
        if (! array_intersect($locationKeys, array_keys($fields))) {
            return $data;
        }

        $existing = [];
        if (! empty($data['id'])) {
            $id = is_array($data['id']) ? reset($data['id']) : $data['id'];
            $existing = $this->find($id) ?? [];
        }

        $parts = [];
        foreach ($locationKeys as $key) {
            $value = $fields[$key] ?? ($existing[$key] ?? '');
            if ($value !== '' && $value !== null) {
                $parts[] = $value;
            }
        }

        $data['data']['location'] = implode(', ', $parts);

        return $data;
    }
}
