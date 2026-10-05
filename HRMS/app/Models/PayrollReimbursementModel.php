<?php

namespace App\Models;

use CodeIgniter\Model;

class PayrollReimbursementModel extends Model
{
    protected $table          = 'payroll_reimbursements';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $useTimestamps  = true;
    protected $useSoftDeletes = true;
    protected $deletedField   = 'deleted_at';
    protected $allowedFields  = [
        'employee_id', 'expense_type', 'amount', 'expense_date', 'attachment_path', 'status', 'approved_by',
        'approved_at', 'payroll_month_id', 'remarks', 'created_by', 'updated_by',
    ];

    protected $validationRules = [
        'employee_id'  => 'required|integer',
        'expense_type' => 'required|max_length[100]',
        'amount'       => 'required|decimal|greater_than[0]',
        'expense_date' => 'required|valid_date',
    ];

    public function forEmployee(int $employeeId): array
    {
        return $this->where('employee_id', $employeeId)->orderBy('expense_date', 'DESC')->findAll();
    }

    public function approvedUnassignedFor(int $employeeId): array
    {
        return $this->where('employee_id', $employeeId)->where('status', 'approved')->where('payroll_month_id', null)->findAll();
    }

    public function withEmployee(): static
    {
        return $this->select("payroll_reimbursements.*, e.employee_code, CONCAT(e.first_name,' ',e.last_name) as employee_name")
            ->join('employees e', 'e.id = payroll_reimbursements.employee_id');
    }
}
