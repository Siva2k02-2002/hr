<?php

namespace App\Models;

use CodeIgniter\Model;

class PayrollLoanModel extends Model
{
    protected $table          = 'payroll_loans';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $useTimestamps  = true;
    protected $useSoftDeletes = true;
    protected $deletedField   = 'deleted_at';
    protected $allowedFields  = [
        'employee_id', 'loan_number', 'loan_type', 'principal_amount', 'interest_rate', 'tenure_months', 'emi_amount',
        'start_month', 'start_year', 'outstanding_balance', 'status', 'created_by', 'updated_by',
    ];

    protected $validationRules = [
        'employee_id'      => 'required|integer',
        'loan_number'      => 'required|max_length[30]',
        'principal_amount' => 'required|decimal',
        'tenure_months'    => 'required|integer|greater_than[0]',
        'emi_amount'       => 'required|decimal',
    ];

    public function findByNumber(string $loanNumber): ?array
    {
        return $this->where('loan_number', $loanNumber)->first();
    }

    public function forEmployee(int $employeeId): array
    {
        return $this->where('employee_id', $employeeId)->orderBy('created_at', 'DESC')->findAll();
    }

    public function withEmployee(): static
    {
        return $this->select("payroll_loans.*, e.employee_code, CONCAT(e.first_name,' ',e.last_name) as employee_name")
            ->join('employees e', 'e.id = payroll_loans.employee_id');
    }
}
