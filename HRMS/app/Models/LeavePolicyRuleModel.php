<?php

namespace App\Models;

use CodeIgniter\Model;

class LeavePolicyRuleModel extends Model
{
    protected $table         = 'leave_policy_rules';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'leave_policy_id', 'leave_type_id', 'annual_allocation', 'accrual_method', 'monthly_accrual_days',
        'carry_forward_allowed', 'carry_forward_limit', 'carry_forward_unlimited', 'encashment_allowed',
        'max_consecutive_days', 'min_days_per_application', 'max_applications_per_year', 'sandwich_rule_applicable',
        'notice_period_days', 'status',
    ];

    protected $validationRules = [
        'leave_policy_id' => 'required|integer',
        'leave_type_id'   => 'required|integer',
    ];

    public function forPolicyAndType(int $policyId, int $leaveTypeId): ?array
    {
        return $this->where('leave_policy_id', $policyId)->where('leave_type_id', $leaveTypeId)->first();
    }

    public function forPolicy(int $policyId): array
    {
        return $this->select('leave_policy_rules.*, lt.name as leave_type_name, lt.code as leave_type_code')
            ->join('leave_types lt', 'lt.id = leave_policy_rules.leave_type_id')
            ->where('leave_policy_id', $policyId)
            ->orderBy('lt.sort_order')
            ->findAll();
    }
}
