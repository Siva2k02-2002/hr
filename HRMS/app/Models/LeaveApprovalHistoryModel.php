<?php

namespace App\Models;

use CodeIgniter\Model;

/** Immutable ledger — insert-only, no update()/delete() calls should ever be made against this model. */
class LeaveApprovalHistoryModel extends Model
{
    protected $table         = 'leave_approval_history';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'leave_application_id', 'action', 'level', 'actor_id', 'actor_role', 'remarks', 'override_reason',
        'snapshot_status', 'created_at',
    ];

    public function forApplication(int $applicationId): array
    {
        return $this->select('leave_approval_history.*, u.name as actor_name')
            ->join('users u', 'u.id = leave_approval_history.actor_id', 'left')
            ->where('leave_application_id', $applicationId)
            ->orderBy('leave_approval_history.created_at', 'ASC')
            ->findAll();
    }
}
