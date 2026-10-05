<?php

namespace App\Models;

use CodeIgniter\Model;

/** Single-row table — foundation-only flat placeholder rate. Master tds_enabled toggle lives on payroll_settings. */
class PayrollTdsSettingModel extends Model
{
    protected $table         = 'payroll_tds_settings';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['default_percentage', 'applicable_above_gross'];

    public function current(): array
    {
        $row = $this->orderBy('id', 'asc')->first();
        if ($row) {
            return $row;
        }

        $id = $this->insert(['default_percentage' => 0, 'applicable_above_gross' => 0], true);

        return $this->find($id);
    }
}
