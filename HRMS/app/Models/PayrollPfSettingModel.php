<?php

namespace App\Models;

use CodeIgniter\Model;

/** Single-row table — master pf_enabled toggle lives on payroll_settings. */
class PayrollPfSettingModel extends Model
{
    protected $table         = 'payroll_pf_settings';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['employee_percentage', 'employer_percentage', 'wage_ceiling', 'pf_wage_basis'];

    public function current(): array
    {
        $row = $this->orderBy('id', 'asc')->first();
        if ($row) {
            return $row;
        }

        $id = $this->insert([
            'employee_percentage' => 12.00, 'employer_percentage' => 12.00,
            'wage_ceiling' => 15000.00, 'pf_wage_basis' => 'basic',
        ], true);

        return $this->find($id);
    }
}
