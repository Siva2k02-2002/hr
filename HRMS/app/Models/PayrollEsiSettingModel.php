<?php

namespace App\Models;

use CodeIgniter\Model;

/** Single-row table — master esi_enabled toggle lives on payroll_settings. */
class PayrollEsiSettingModel extends Model
{
    protected $table         = 'payroll_esi_settings';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['employee_percentage', 'employer_percentage', 'wage_ceiling'];

    public function current(): array
    {
        $row = $this->orderBy('id', 'asc')->first();
        if ($row) {
            return $row;
        }

        $id = $this->insert([
            'employee_percentage' => 0.75, 'employer_percentage' => 3.25, 'wage_ceiling' => 21000.00,
        ], true);

        return $this->find($id);
    }
}
