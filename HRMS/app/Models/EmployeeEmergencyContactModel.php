<?php

namespace App\Models;

use CodeIgniter\Model;

class EmployeeEmergencyContactModel extends Model
{
    protected $table         = 'employee_emergency_contacts';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $useSoftDeletes = true;
    protected $deletedField  = 'deleted_at';
    protected $allowedFields = ['employee_id', 'name', 'relationship', 'phone', 'alternate_phone', 'address', 'priority'];

    protected $validationRules = [
        'employee_id'  => 'required|integer',
        'name'         => 'required|min_length[2]|max_length[150]',
        'relationship' => 'required|max_length[60]',
        'phone'        => 'required|regex_match[/^[0-9]{10}$/]',
    ];

    public function forEmployee(int $employeeId): array
    {
        return $this->where('employee_id', $employeeId)->orderBy('priority')->findAll();
    }
}
