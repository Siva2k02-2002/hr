<?php

namespace App\Services;

use App\Models\LeaveEncashmentModel;
use App\Models\LeavePolicyModel;
use App\Models\LeavePolicyRuleModel;
use App\Models\LeaveSettingModel;
use App\Models\LeaveTypeModel;
use RuntimeException;

/** Foundation only — amount_placeholder is never payroll-computed here. See LeaveBalanceService::debitForEncashment(). */
class LeaveEncashmentService
{
    private LeaveEncashmentModel $encashments;

    public function __construct(
        private AuditService $audit = new AuditService(),
        private LeaveBalanceService $balances = new LeaveBalanceService()
    ) {
        $this->encashments = new LeaveEncashmentModel(service('tenantContext')->db());
    }

    public function request(array $employee, int $leaveTypeId, float $days, ?int $createdBy = null): int
    {
        $db       = service('tenantContext')->db();
        $settings = (new LeaveSettingModel($db))->current();
        if (! $settings['leave_encashment_enabled']) {
            throw new RuntimeException('Leave encashment is disabled in leave settings.');
        }

        $leaveType = (new LeaveTypeModel($db))->find($leaveTypeId);
        if (! $leaveType || ! $leaveType['encashment_allowed']) {
            throw new RuntimeException('This leave type does not allow encashment.');
        }

        $policy     = (new LeavePolicyModel($db))->resolveFor($employee);
        $policyRule = $policy ? (new LeavePolicyRuleModel($db))->forPolicyAndType((int) $policy['id'], $leaveTypeId) : null;
        if ($policyRule !== null && ! $policyRule['encashment_allowed']) {
            throw new RuntimeException('Your leave policy does not allow encashment for this leave type.');
        }

        $financialYear = leave_financial_year();
        $balance       = $this->balances->projectedBalance((int) $employee['id'], $leaveTypeId, $financialYear, $policyRule);
        if ($days <= 0 || $days > $balance) {
            throw new RuntimeException('Requested encashment days exceed the available balance.');
        }

        $data = [
            'employee_id' => $employee['id'], 'leave_type_id' => $leaveTypeId, 'financial_year' => $financialYear,
            'days_encashed' => $days, 'status' => 'pending', 'requested_at' => date('Y-m-d H:i:s'), 'created_by' => $createdBy,
        ];
        $id = $this->encashments->insert($data, true);
        $this->audit->log('leave_encashment_request', 'leave', 'leave_encashment', $id, null, $data);

        return $id;
    }

    public function approve(int $id, int $actorId): void
    {
        $row = $this->assertPending($id);

        $this->balances->debitForEncashment((int) $row['employee_id'], (int) $row['leave_type_id'], (int) $row['financial_year'], (float) $row['days_encashed']);
        $this->encashments->update($id, ['status' => 'approved', 'approved_by' => $actorId, 'approved_at' => date('Y-m-d H:i:s')]);
        $this->audit->log('leave_encashment_approve', 'leave', 'leave_encashment', $id, $row, ['status' => 'approved']);
    }

    public function reject(int $id, int $actorId, ?string $remarks = null): void
    {
        $row = $this->assertPending($id);

        $this->encashments->update($id, ['status' => 'rejected', 'approved_by' => $actorId, 'approved_at' => date('Y-m-d H:i:s'), 'remarks' => $remarks]);
        $this->audit->log('leave_encashment_reject', 'leave', 'leave_encashment', $id, $row, ['status' => 'rejected']);
    }

    private function assertPending(int $id): array
    {
        $row = $this->encashments->find($id);
        if (! $row) {
            throw new RuntimeException('Encashment request not found.');
        }
        if ($row['status'] !== 'pending') {
            throw new RuntimeException('This request has already been reviewed.');
        }

        return $row;
    }
}
