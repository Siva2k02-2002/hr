<?php

namespace App\Models;

use CodeIgniter\Model;

class PlatformPermissionModel extends Model
{
    protected $table         = 'platform_permissions';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['slug', 'module', 'description'];

    public function groupedByModule(): array
    {
        $rows = $this->orderBy('module', 'ASC')->orderBy('slug', 'ASC')->findAll();

        $grouped = [];
        foreach ($rows as $row) {
            $grouped[$row['module']][] = $row;
        }

        return $grouped;
    }
}
