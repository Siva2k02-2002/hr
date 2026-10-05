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
        'user_id', 'employee_id', 'action', 'module', 'record_type', 'record_id',
        'old_values', 'new_values', 'ip_address', 'user_agent', 'created_at',
    ];

    public function withUserName()
    {
        return $this->select('audit_logs.*, u.name as user_name')
            ->join('users u', 'u.id = audit_logs.user_id', 'left');
    }

    public function forRecord(string $module, int $recordId, int $limit = 50): array
    {
        return $this->withUserName()
            ->where('audit_logs.module', $module)
            ->where('audit_logs.record_id', $recordId)
            ->orderBy('audit_logs.id', 'DESC')
            ->limit($limit)
            ->findAll();
    }
}
