<?php

namespace App\Models;

use CodeIgniter\Model;

class LeaveDelegationModel extends Model
{
    protected $table         = 'leave_delegations';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'leave_application_id', 'delegate_employee_id', 'from_date', 'to_date', 'notes', 'notified_at', 'created_by',
    ];

    protected $validationRules = [
        'leave_application_id' => 'required|integer',
        'delegate_employee_id' => 'required|integer',
        'from_date'            => 'required|valid_date',
        'to_date'              => 'required|valid_date',
    ];

    public function forApplication(int $applicationId): ?array
    {
        return $this->select("leave_delegations.*, CONCAT(e.first_name, ' ', e.last_name) as delegate_name")
            ->join('employees e', 'e.id = leave_delegations.delegate_employee_id')
            ->where('leave_application_id', $applicationId)
            ->first();
    }
}
