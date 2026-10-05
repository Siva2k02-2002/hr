<?php

namespace App\Models;

use CodeIgniter\Model;

class PayrollBonusModel extends Model
{
    protected $table         = 'payroll_bonus';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'bonus_batch_id', 'employee_id', 'bonus_type', 'amount', 'payroll_month_id', 'remarks', 'status',
        'created_by', 'updated_by',
    ];

    protected $validationRules = [
        'employee_id' => 'required|integer',
        'bonus_type'  => 'required|in_list[festival,annual,performance,other]',
        'amount'      => 'required|decimal|greater_than[0]',
    ];

    public function forEmployee(int $employeeId): array
    {
        return $this->where('employee_id', $employeeId)->orderBy('created_at', 'DESC')->findAll();
    }

    /** Duplicate-bonus guard: same employee + month + type already recorded. */
    public function existsFor(int $employeeId, ?int $payrollMonthId, string $bonusType): bool
    {
        return $this->where('employee_id', $employeeId)
            ->where('payroll_month_id', $payrollMonthId)
            ->where('bonus_type', $bonusType)
            ->countAllResults() > 0;
    }

    public function forMonthUnassigned(int $payrollMonthId): array
    {
        return $this->where('payroll_month_id', $payrollMonthId)->where('status', 'pending')->findAll();
    }

    /** Pending entries for $employeeId that are either explicitly tagged to $payrollMonthId or not yet tagged to any month at all (picked up by whichever run generates first). */
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
        return $this->select("payroll_bonus.*, e.employee_code, CONCAT(e.first_name,' ',e.last_name) as employee_name")
            ->join('employees e', 'e.id = payroll_bonus.employee_id');
    }
}
