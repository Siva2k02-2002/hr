<?php

namespace App\Models;

use CodeIgniter\Model;

class EmployeeEducationModel extends Model
{
    protected $table         = 'employee_education';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $useSoftDeletes = true;
    protected $deletedField  = 'deleted_at';
    protected $allowedFields = [
        'employee_id', 'qualification', 'institution', 'board_university', 'percentage_cgpa', 'year_of_passing', 'certificate_path',
    ];

    protected $validationRules = [
        'employee_id'   => 'required|integer',
        'qualification' => 'required|max_length[150]',
        'institution'   => 'required|max_length[200]',
    ];

    public function forEmployee(int $employeeId): array
    {
        return $this->where('employee_id', $employeeId)->orderBy('year_of_passing', 'DESC')->findAll();
    }
}
