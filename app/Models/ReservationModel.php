<?php

namespace App\Models;

use CodeIgniter\Model;

class ReservationModel extends Model
{
    protected $table            = 'reservations';
    protected $primaryKey       = 'reservation_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'user_id', 'reservation_date', 'claim_date', 'total_amount', 'payment_status', 'status',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public const STATUSES = ['Pending', 'Confirmed', 'Ready', 'Claimed', 'Cancelled'];

    public function forUser(int $userId): array
    {
        return $this->where('user_id', $userId)->orderBy('created_at', 'DESC')->findAll();
    }

    public function withCustomer()
    {
        return $this->select('reservations.*, users.name AS customer_name, users.customer_type, users.email, users.avatar AS customer_avatar')
            ->join('users', 'users.user_id = reservations.user_id');
    }

    /**
     * Reservations whose claim date has already passed but are still
     * sitting in an active status — the customer never showed up (or the
     * owner never followed up). Distinct from the general "needs
     * attention" alert: this is specifically the overdue subset of it,
     * surfaced separately in the topbar notification dropdown since it's
     * the more urgent case.
     */
    public function overdueCount(): int
    {
        return $this->whereIn('status', ['Pending', 'Confirmed', 'Ready'])
            ->where('claim_date <', date('Y-m-d'))
            ->countAllResults();
    }

    public function counts(): array
    {
        return [
            'total'     => $this->countAllResults(),
            'pending'   => $this->where('status', 'Pending')->countAllResults(),
            'confirmed' => $this->where('status', 'Confirmed')->countAllResults(),
            'ready'     => $this->where('status', 'Ready')->countAllResults(),
            'claimed'   => $this->where('status', 'Claimed')->countAllResults(),
            'cancelled' => $this->where('status', 'Cancelled')->countAllResults(),
        ];
    }
}
