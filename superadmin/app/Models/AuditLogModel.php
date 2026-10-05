<?php

namespace App\Models;

use CodeIgniter\Model;

class AuditLogModel extends Model
{
    protected $table         = 'audit_logs';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'platform_user_id', 'company_id', 'action', 'module', 'record_type', 'record_id',
        'old_values', 'new_values', 'ip_address', 'user_agent', 'created_at',
    ];

    public function withActor()
    {
        return $this->select('audit_logs.*, platform_users.name as actor_name, companies.name as company_name')
            ->join('platform_users', 'platform_users.id = audit_logs.platform_user_id', 'left')
            ->join('companies', 'companies.id = audit_logs.company_id', 'left');
    }
}
