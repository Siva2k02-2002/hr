<?php

namespace App\Models;

use CodeIgniter\Model;

class EmployeeFamilyMemberModel extends Model
{
    protected $table         = 'employee_family_members';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $useSoftDeletes = true;
    protected $deletedField  = 'deleted_at';
    protected $allowedFields = ['employee_id', 'relationship', 'name', 'dob', 'occupation', 'is_dependent', 'is_nominee'];

    protected $validationRules = [
        'employee_id'  => 'required|integer',
        'name'         => 'required|min_length[2]|max_length[150]',
        'relationship' => 'required|max_length[60]',
    ];

    public function forEmployee(int $employeeId): array
    {
        return $this->where('employee_id', $employeeId)->orderBy('id')->findAll();
    }
}
