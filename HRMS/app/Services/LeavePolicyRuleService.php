<?php

namespace App\Services;

use App\Models\LeavePolicyRuleModel;

/** Bulk save-all-rules-for-a-policy — one row per leave_type per policy, mirrors RolesController::savePermissions()'s replace-the-whole-set pattern. */
class LeavePolicyRuleService
{
    private LeavePolicyRuleModel $rules;

    public function __construct(private AuditService $audit = new AuditService())
    {
        $this->rules = new LeavePolicyRuleModel(service('tenantContext')->db());
    }

    /**
     * @param array<int, array<string, mixed>> $rulesByLeaveTypeId keyed by leave_type_id
     */
    public function saveAll(int $policyId, array $rulesByLeaveTypeId): void
    {
        $old = $this->rules->forPolicy($policyId);

        foreach ($rulesByLeaveTypeId as $leaveTypeId => $rule) {
            $rule['leave_policy_id'] = $policyId;
            $rule['leave_type_id']   = $leaveTypeId;

            $existing = $this->rules->forPolicyAndType($policyId, (int) $leaveTypeId);
            $existing ? $this->rules->update($existing['id'], $rule) : $this->rules->insert($rule);
        }

        $this->audit->log('leave_policy_rules_update', 'leave', 'leave_policy', $policyId, ['rules' => $old], ['rules' => $rulesByLeaveTypeId]);
    }
}
