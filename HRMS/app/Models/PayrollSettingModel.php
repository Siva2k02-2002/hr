<?php

namespace App\Models;

use CodeIgniter\Model;

/** Single-row table — same pattern as AttendanceSettingModel/LeaveSettingModel. */
class PayrollSettingModel extends Model
{
    protected $table         = 'payroll_settings';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'payroll_start_day', 'payroll_end_day', 'salary_payment_day', 'financial_year_start_month', 'currency',
        'working_days_basis', 'fixed_working_days', 'overtime_enabled', 'overtime_multiplier', 'overtime_rate_basis',
        'lop_enabled', 'lop_deduction_basis', 'pf_enabled', 'esi_enabled', 'pt_enabled', 'tds_enabled',
        'payslip_prefix', 'lock_after_approval',
    ];

    public function current(): array
    {
        $row = $this->orderBy('id', 'asc')->first();
        if ($row) {
            return $row;
        }

        $id = $this->insert([
            'payroll_start_day' => 1, 'payroll_end_day' => 31, 'salary_payment_day' => 7,
            'financial_year_start_month' => 4, 'currency' => 'INR', 'working_days_basis' => 'calendar',
            'fixed_working_days' => 30, 'overtime_enabled' => 1, 'overtime_multiplier' => 1.50,
            'overtime_rate_basis' => 'basic', 'lop_enabled' => 1, 'lop_deduction_basis' => 'gross',
            'pf_enabled' => 1, 'esi_enabled' => 1, 'pt_enabled' => 1, 'tds_enabled' => 1,
            'payslip_prefix' => 'PAY', 'lock_after_approval' => 1,
        ], true);

        return $this->find($id);
    }
}
