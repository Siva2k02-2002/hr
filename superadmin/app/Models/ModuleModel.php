<?php

namespace App\Models;

use CodeIgniter\Model;

class ModuleModel extends Model
{
    protected $table         = 'modules';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['module_key', 'name', 'description', 'is_core'];

    protected $validationRules = [
        'module_key' => 'required|alpha_dash|max_length[50]',
        'name'       => 'required|min_length[2]|max_length[100]',
    ];
}
