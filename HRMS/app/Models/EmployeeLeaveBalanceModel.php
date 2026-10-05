<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * The balance ledger. Rows are mutated by delta arithmetic on closing_balance
 * by LeaveBalanceService — never recomputed via SUM() over applications at
 * read time (explicit spec requirement).
 */
class EmployeeLeaveBalanceModel extends Model
{
    protected $table         = 'employee_leave_balances';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'employee_id', 'leave_type_id', 'leave_policy_id', 'financial_year', 'opening_balance', 'earned',
        'availed', 'adjusted', 'carry_forward_in', 'carry_forward_out', 'encashed', 'closing_balance',
        'last_transaction_at',
    ];

    public function forEmployeeTypeYear(int $employeeId, int $leaveTypeId, int $financialYear): ?array
    {
        return $this->where('employee_id', $employeeId)
            ->where('leave_type_id', $leaveTypeId)
            ->where('financial_year', $financialYear)
            ->first();
    }

    public function forEmployee(int $employeeId, int $financialYear): array
    {
        return $this->select('employee_leave_balances.*, lt.name as leave_type_name, lt.code as leave_type_code, lt.color as leave_type_color')
            ->join('leave_types lt', 'lt.id = employee_leave_balances.leave_type_id')
            ->where('employee_id', $employeeId)
            ->where('financial_year', $financialYear)
            ->orderBy('lt.sort_order')
            ->findAll();
    }

    /**
     * Row-locked read for the balance-mutation methods in LeaveBalanceService. Only
     * takes an actual row lock when called inside a transaction (transStart/transComplete)
     * — see LeaveBalanceService::deductOnApproval()/restoreOnCancellation().
     */
    public function lockForUpdate(int $id): ?array
    {
        return $this->db->query('SELECT * FROM employee_leave_balances WHERE id = ? FOR UPDATE', [$id])->getRowArray();
    }
}
