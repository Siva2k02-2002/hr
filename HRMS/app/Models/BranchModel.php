<?php

namespace App\Models;

use CodeIgniter\Model;

class BranchModel extends Model
{
    protected $table          = 'branches';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $useTimestamps  = true;
    protected $useSoftDeletes = true;
    protected $deletedField   = 'deleted_at';
    protected $allowedFields  = ['name', 'code', 'address', 'state', 'phone', 'email', 'manager_user_id', 'status'];

    /** No is_unique here — see UserModel's note on why; checked by hand in BranchService. */
    protected $validationRules = [
        'name' => 'required|min_length[2]|max_length[150]',
        'code' => 'required|alpha_numeric_punct|max_length[30]',
    ];

    public function withManagerName()
    {
        return $this->select('branches.*, u.name as manager_name')
            ->join('users u', 'u.id = branches.manager_user_id', 'left');
    }
}
