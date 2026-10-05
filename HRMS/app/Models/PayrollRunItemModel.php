<?php

namespace App\Models;

use CodeIgniter\Model;

class PayrollRunItemModel extends Model
{
    protected $table         = 'payroll_run_items';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'payroll_run_id', 'payroll_month_id', 'employee_id', 'salary_structure_id', 'settlement_type',
        'working_days', 'present_days', 'paid_leave_days', 'lop_days', 'half_days', 'overtime_hours', 'overtime_amount',
        'gross_earnings', 'gross_deductions', 'net_salary', 'pf_employee', 'pf_employer', 'esi_employee', 'esi_employer',
        'professional_tax', 'tds', 'loan_deduction', 'advance_deduction', 'bonus_amount', 'incentive_amount',
        'reimbursement_amount', 'arrears_amount', 'earnings_breakdown', 'deductions_breakdown', 'status', 'remarks',
        'created_by', 'updated_by',
    ];

    public function forRun(int $runId): array
    {
        return $this->select("payroll_run_items.*, e.employee_code, CONCAT(e.first_name,' ',e.last_name) as employee_name, e.branch_id, e.department_id")
            ->join('employees e', 'e.id = payroll_run_items.employee_id')
            ->where('payroll_run_id', $runId)
            ->orderBy('e.first_name')
            ->findAll();
    }

    public function forEmployeeAndRun(int $employeeId, int $runId): ?array
    {
        return $this->where('employee_id', $employeeId)->where('payroll_run_id', $runId)->first();
    }

    public function forEmployee(int $employeeId): array
    {
        return $this->select('payroll_run_items.*, m.month, m.year')
            ->join('payroll_months m', 'm.id = payroll_run_items.payroll_month_id')
            ->where('employee_id', $employeeId)
            ->orderBy('m.year', 'DESC')->orderBy('m.month', 'DESC')
            ->findAll();
    }

    /** This employee's lines on runs that are still draft/generated — not yet approved, so still safe to cancel and regenerate. */
    public function openForEmployee(int $employeeId): array
    {
        return $this->select('payroll_run_items.id, payroll_run_items.earnings_breakdown')
            ->join('payroll_runs pr', 'pr.id = payroll_run_items.payroll_run_id')
            ->where('payroll_run_items.employee_id', $employeeId)
            ->whereIn('pr.status', ['draft', 'generated'])
            ->findAll();
    }
}
