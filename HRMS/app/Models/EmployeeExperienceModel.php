<?php

namespace App\Models;

use CodeIgniter\Model;

class EmployeeExperienceModel extends Model
{
    protected $table         = 'employee_experience';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $useSoftDeletes = true;
    protected $deletedField  = 'deleted_at';
    protected $allowedFields = [
        'employee_id', 'company_name', 'designation', 'from_date', 'to_date', 'years_experience',
        'reason_for_leaving', 'experience_letter_path',
    ];

    protected $validationRules = [
        'employee_id'  => 'required|integer',
        'company_name' => 'required|max_length[200]',
        'from_date'    => 'required|valid_date',
    ];

    public function forEmployee(int $employeeId): array
    {
        return $this->where('employee_id', $employeeId)->orderBy('from_date', 'DESC')->findAll();
    }
}
