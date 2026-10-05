<?php

namespace App\Models;

use CodeIgniter\Model;

class PlatformSettingModel extends Model
{
    protected $table         = 'platform_settings';
    protected $primaryKey    = 'setting_key';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = ['setting_key', 'setting_value', 'updated_at'];

    public function get(string $key, ?string $default = null): ?string
    {
        $row = $this->find($key);

        return $row['setting_value'] ?? $default;
    }

    public function setValue(string $key, string $value): void
    {
        $this->db->table($this->table)->replace([
            'setting_key'   => $key,
            'setting_value' => $value,
            'updated_at'    => date('Y-m-d H:i:s'),
        ]);
    }
}
