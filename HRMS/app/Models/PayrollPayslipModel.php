<?php

namespace App\Models;

use CodeIgniter\Model;

class PayrollPayslipModel extends Model
{
    protected $table         = 'payroll_payslips';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'payroll_run_item_id', 'employee_id', 'payroll_month_id', 'payslip_number',
        'generated_at', 'downloaded_at', 'downloaded_count', 'created_by',
    ];

    public function forRunItem(int $runItemId): ?array
    {
        return $this->where('payroll_run_item_id', $runItemId)->first();
    }

    public function forEmployee(int $employeeId): array
    {
        return $this->select('payroll_payslips.*, m.month, m.year')
            ->join('payroll_months m', 'm.id = payroll_payslips.payroll_month_id')
            ->where('employee_id', $employeeId)
            ->orderBy('m.year', 'DESC')->orderBy('m.month', 'DESC')
            ->findAll();
    }
}
