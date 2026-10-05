<?php

namespace App\Models;

use CodeIgniter\Model;

class DepartmentModel extends Model
{
    protected $table          = 'departments';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $useTimestamps  = true;
    protected $useSoftDeletes = true;
    protected $deletedField   = 'deleted_at';
    protected $allowedFields  = ['name', 'code', 'branch_id', 'head_user_id', 'status'];

    /** No is_unique here — see UserModel's note on why; checked by hand in DepartmentService. */
    protected $validationRules = [
        'name'      => 'required|min_length[2]|max_length[150]',
        'code'      => 'required|alpha_numeric_punct|max_length[30]',
        'branch_id' => 'required|integer',
    ];

    public function withRelations()
    {
        return $this->select('departments.*, b.name as branch_name, u.name as head_name')
            ->join('branches b', 'b.id = departments.branch_id')
            ->join('users u', 'u.id = departments.head_user_id', 'left');
    }
}
