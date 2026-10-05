<?php

namespace App\Models;

use CodeIgniter\Model;

/** Append-only — see EmployeeStatusService, the only writer. */
class EmployeeStatusHistoryModel extends Model
{
    protected $table          = 'employee_status_history';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $useTimestamps  = false; // created_at is set explicitly on insert; no updates ever happen to a history row
    protected $allowedFields  = ['employee_id', 'event_type', 'from_value', 'to_value', 'remarks', 'changed_by', 'created_at'];

    public function forEmployee(int $employeeId): array
    {
        return $this->select('employee_status_history.*, u.name as changed_by_name')
            ->join('users u', 'u.id = employee_status_history.changed_by', 'left')
            ->where('employee_status_history.employee_id', $employeeId)
            ->orderBy('employee_status_history.id', 'DESC')
            ->findAll();
    }
}
