<?php

namespace App\Models;

use CodeIgniter\Model;

class CompanyDatabaseConnectionModel extends Model
{
    protected $table         = 'company_database_connections';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'company_id', 'db_host', 'db_port', 'db_name', 'db_username', 'db_password_enc',
        'status', 'last_checked_at',
    ];

    public function forCompany(int $companyId): ?array
    {
        return $this->where('company_id', $companyId)->first();
    }
}
