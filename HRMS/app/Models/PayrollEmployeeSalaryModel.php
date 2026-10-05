<?php

namespace App\Models;

use CodeIgniter\Model;

/** Assignment history — a new assign() inserts a new row and closes the previous one's effective_to; rows are never overwritten in place except to close them out. */
class PayrollEmployeeSalaryModel extends Model
{
    protected $table          = 'payroll_employee_salary';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $useTimestamps  = true;
    protected $useSoftDeletes = true;
    protected $deletedField   = 'deleted_at';
    protected $allowedFields  = [
        'employee_id', 'salary_structure_id', 'effective_from', 'effective_to', 'gross_salary', 'ctc',
        'status', 'created_by', 'updated_by',
    ];

    protected $validationRules = [
        'employee_id'         => 'required|integer',
        'salary_structure_id' => 'required|integer',
        'effective_from'      => 'required|valid_date',
        'gross_salary'        => 'required|decimal',
        'ctc'                 => 'required|decimal',
    ];

    /**
     * The assignment in effect for $employeeId on $date (default: today). Falls back to the
     * most recent assignment that had already started by $date if none matches the
     * effective_to window exactly — covers a row whose effective_to was left open-ended
     * but for some reason didn't match above (e.g. a null comparison edge case), without
     * ever picking an assignment that starts *after* $date (audit finding PAY-05: the old
     * fallback dropped the effective_from bound entirely and could apply a future salary
     * revision to a past payroll run).
     */
    public function activeFor(int $employeeId, ?string $date = null): ?array
    {
        $date ??= date('Y-m-d');

        $row = $this->where('employee_id', $employeeId)
            ->where('status', 'active')
            ->where('effective_from <=', $date)
            ->groupStart()->where('effective_to >=', $date)->orWhere('effective_to', null)->groupEnd()
            ->orderBy('effective_from', 'DESC')
            ->first();

        return $row ?? $this->where('employee_id', $employeeId)
            ->where('status', 'active')
            ->where('effective_from <=', $date)
            ->orderBy('effective_from', 'DESC')
            ->first();
    }

    public function forEmployee(int $employeeId): array
    {
        return $this->select('payroll_employee_salary.*, s.name as structure_name')
            ->join('payroll_salary_structures s', 's.id = payroll_employee_salary.salary_structure_id')
            ->where('employee_id', $employeeId)
            ->orderBy('effective_from', 'DESC')
            ->findAll();
    }

    public function withEmployee(): static
    {
        return $this->select("
                payroll_employee_salary.*, s.name as structure_name,
                e.employee_code, CONCAT(e.first_name, ' ', e.last_name) as employee_name, e.branch_id, e.department_id
            ")
            ->join('payroll_salary_structures s', 's.id = payroll_employee_salary.salary_structure_id')
            ->join('employees e', 'e.id = payroll_employee_salary.employee_id');
    }
}
