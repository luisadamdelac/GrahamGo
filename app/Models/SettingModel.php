<?php

namespace App\Models;

use CodeIgniter\Model;

class SettingModel extends Model
{
    protected $table         = 'settings';
    protected $primaryKey    = 'setting_key';
    protected $useAutoIncrement = false;
    protected $returnType    = 'array';
    protected $allowedFields = ['setting_key', 'setting_value'];

    protected $useTimestamps = true;
    protected $createdField  = '';
    protected $updatedField  = 'updated_at';

    public function getValue(string $key, ?string $default = null): ?string
    {
        $row = $this->find($key);

        return $row['setting_value'] ?? $default;
    }

    public function setValue(string $key, ?string $value): void
    {
        $this->save([
            'setting_key'   => $key,
            'setting_value' => $value,
        ]);
    }

    public function getMany(array $keys): array
    {
        $rows = $this->whereIn('setting_key', $keys)->findAll();
        $map  = [];
        foreach ($rows as $row) {
            $map[$row['setting_key']] = $row['setting_value'];
        }

        foreach ($keys as $key) {
            $map[$key] ??= null;
        }

        return $map;
    }
}
