<?php

namespace App\Models;

use CodeIgniter\Model;

class DesignationModel extends Model
{
    protected $table          = 'designations';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $useTimestamps  = true;
    protected $useSoftDeletes = true;
    protected $deletedField   = 'deleted_at';
    protected $allowedFields  = ['name', 'department_id', 'level', 'status'];

    protected $validationRules = [
        'name'          => 'required|min_length[2]|max_length[150]',
        'department_id' => 'required|integer',
    ];

    public function withDepartmentName()
    {
        return $this->select('designations.*, d.name as department_name')
            ->join('departments d', 'd.id = designations.department_id');
    }
}
