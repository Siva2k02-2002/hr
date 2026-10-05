<?php

namespace App\Models;

use CodeIgniter\Model;

/** Append-only run history — see LeaveCarryForwardService. */
class LeaveCarryForwardHistoryModel extends Model
{
    protected $table         = 'leave_carry_forward_history';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'employee_id', 'leave_type_id', 'from_financial_year', 'to_financial_year', 'eligible_balance',
        'carried_forward_days', 'expired_days', 'carry_forward_rule_limit', 'expiry_date', 'processed_by',
        'run_batch_id', 'created_at',
    ];

    public function forBatch(string $batchId): array
    {
        return $this->select("leave_carry_forward_history.*, CONCAT(e.first_name, ' ', e.last_name) as employee_name, e.employee_code, lt.name as leave_type_name")
            ->join('employees e', 'e.id = leave_carry_forward_history.employee_id')
            ->join('leave_types lt', 'lt.id = leave_carry_forward_history.leave_type_id')
            ->where('run_batch_id', $batchId)
            ->findAll();
    }

    public function recentBatches(int $limit = 20): array
    {
        return $this->select('run_batch_id, from_financial_year, to_financial_year, MIN(created_at) as run_at, COUNT(*) as employee_count, SUM(carried_forward_days) as total_carried')
            ->groupBy('run_batch_id, from_financial_year, to_financial_year')
            ->orderBy('run_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }
}
