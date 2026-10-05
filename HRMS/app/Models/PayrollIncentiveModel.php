<?php

namespace App\Models;

use CodeIgniter\Model;

class PayrollIncentiveModel extends Model
{
    protected $table         = 'payroll_incentives';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['employee_id', 'incentive_type', 'amount', 'payroll_month_id', 'remarks', 'status', 'created_by', 'updated_by'];

    protected $validationRules = [
        'employee_id'    => 'required|integer',
        'incentive_type' => 'required|max_length[100]',
        'amount'         => 'required|decimal|greater_than[0]',
    ];

    public function forEmployee(int $employeeId): array
    {
        return $this->where('employee_id', $employeeId)->orderBy('created_at', 'DESC')->findAll();
    }

    public function forMonthUnassigned(int $payrollMonthId): array
    {
        return $this->where('payroll_month_id', $payrollMonthId)->where('status', 'pending')->findAll();
    }

    /** Pending entries for $employeeId that are either explicitly tagged to $payrollMonthId or not yet tagged to any month at all. */
    public function forEmployeeAndMonthOrUnassigned(int $employeeId, ?int $payrollMonthId): array
    {
        $query = $this->where('employee_id', $employeeId)->where('status', 'pending');
        if ($payrollMonthId !== null) {
            $query->groupStart()->where('payroll_month_id', $payrollMonthId)->orWhere('payroll_month_id', null)->groupEnd();
        } else {
            $query->where('payroll_month_id', null);
        }

        return $query->findAll();
    }

    public function withEmployee(): static
    {
        return $this->select("payroll_incentives.*, e.employee_code, CONCAT(e.first_name,' ',e.last_name) as employee_name")
            ->join('employees e', 'e.id = payroll_incentives.employee_id');
    }
}
