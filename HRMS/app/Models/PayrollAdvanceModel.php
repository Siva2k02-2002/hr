<?php

namespace App\Models;

use CodeIgniter\Model;

class PayrollAdvanceModel extends Model
{
    protected $table          = 'payroll_advances';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $useTimestamps  = true;
    protected $useSoftDeletes = true;
    protected $deletedField   = 'deleted_at';
    protected $allowedFields  = [
        'employee_id', 'amount', 'advance_date', 'recovery_type', 'installments_count', 'installment_amount',
        'recovered_amount', 'remaining_balance', 'status', 'reason', 'created_by', 'updated_by',
    ];

    protected $validationRules = [
        'employee_id'   => 'required|integer',
        'amount'        => 'required|decimal|greater_than[0]',
        'advance_date'  => 'required|valid_date',
    ];

    public function forEmployee(int $employeeId): array
    {
        return $this->where('employee_id', $employeeId)->orderBy('advance_date', 'DESC')->findAll();
    }

    public function withEmployee(): static
    {
        return $this->select("payroll_advances.*, e.employee_code, CONCAT(e.first_name,' ',e.last_name) as employee_name")
            ->join('employees e', 'e.id = payroll_advances.employee_id');
    }
}
