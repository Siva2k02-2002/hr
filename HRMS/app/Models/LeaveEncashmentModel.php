<?php

namespace App\Models;

use CodeIgniter\Model;

/** Foundation only — amount_placeholder is never payroll-computed here. */
class LeaveEncashmentModel extends Model
{
    protected $table          = 'leave_encashments';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $useTimestamps  = true;
    protected $useSoftDeletes = true;
    protected $deletedField   = 'deleted_at';
    protected $allowedFields  = [
        'employee_id', 'leave_type_id', 'financial_year', 'days_encashed', 'amount_placeholder', 'status',
        'requested_at', 'approved_by', 'approved_at', 'remarks', 'created_by',
    ];

    protected $validationRules = [
        'employee_id'   => 'required|integer',
        'leave_type_id' => 'required|integer',
        'days_encashed' => 'required|greater_than[0]',
        'status'        => 'required|in_list[pending,approved,rejected,paid]',
    ];

    public function withEmployee()
    {
        return $this->select("
                leave_encashments.*,
                CONCAT(e.first_name, ' ', e.last_name) as employee_name, e.employee_code,
                lt.name as leave_type_name, lt.code as leave_type_code
            ")
            ->join('employees e', 'e.id = leave_encashments.employee_id')
            ->join('leave_types lt', 'lt.id = leave_encashments.leave_type_id');
    }
}
