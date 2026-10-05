<?php

namespace App\Services;

use App\Models\EmployeeModel;
use App\Models\LeaveApplicationDayModel;
use App\Models\LeaveApplicationModel;
use App\Models\LeaveApprovalHistoryModel;
use App\Models\LeaveDelegationModel;
use App\Models\LeavePolicyModel;
use App\Models\LeavePolicyRuleModel;
use App\Models\LeaveSettingModel;
use App\Models\LeaveTypeModel;
use App\Models\UserModel;
use RuntimeException;

/**
 * Owns apply/submit/cancel. Every day-count and eligibility number here is
 * server-computed or server-validated — the client's own arithmetic is
 * never trusted (see LeaveCalculationService).
 */
class LeaveApplicationService
{
    private LeaveApplicationModel $applications;

    public function __construct(
        private AuditService $audit = new AuditService(),
        private LeaveCalculationService $calculator = new LeaveCalculationService(),
        private LeaveBalanceService $balanceService = new LeaveBalanceService(),
        private LeaveApprovalService $approvals = new LeaveApprovalService(),
        private LeaveAttendanceIntegrationService $attendance = new LeaveAttendanceIntegrationService(),
        private NotificationService $notifications = new NotificationService()
    ) {
        $this->applications = new LeaveApplicationModel(service('tenantContext')->db());
    }

    /**
     * @param array $data employee_id, leave_type_id, from_date, to_date, is_half_day, half_day_session,
     *                     reason, emergency_contact_name, emergency_contact_phone, attachment_path,
     *                     is_emergency, delegate_employee_id, delegation_notes, created_by
     */
    public function submit(array $data): int
    {
        $db       = service('tenantContext')->db();
        $employee = (new EmployeeModel($db))->find($data['employee_id']);
        if (! $employee) {
            throw new RuntimeException('Employee not found.');
        }

        $leaveType = (new LeaveTypeModel($db))->find($data['leave_type_id']);
        if (! $leaveType || $leaveType['status'] !== 'active') {
            throw new RuntimeException('Leave type not found or inactive.');
        }

        $settings   = (new LeaveSettingModel($db))->current();
        $policy     = (new LeavePolicyModel($db))->resolveFor($employee);
        $policyRule = $policy ? (new LeavePolicyRuleModel($db))->forPolicyAndType((int) $policy['id'], (int) $leaveType['id']) : null;
        $rule       = $this->effectiveRule($leaveType, $policyRule);

        $isHalfDay = ! empty($data['is_half_day']);
        if ($isHalfDay && (! $settings['half_day_enabled'] || ! $leaveType['half_day_allowed'])) {
            throw new RuntimeException('Half-day leave is not allowed for this leave type.');
        }

        if (strtotime($data['from_date']) < strtotime(date('Y-m-d'))) {
            throw new RuntimeException('Leave cannot be applied for a past date.');
        }
        if ((int) $settings['max_future_apply_days'] > 0) {
            $daysAhead = (int) round((strtotime($data['from_date']) - strtotime(date('Y-m-d'))) / 86400);
            if ($daysAhead > (int) $settings['max_future_apply_days']) {
                throw new RuntimeException('This date is too far in the future to apply for leave.');
            }
        }

        $calc = $this->calculator->calculate(
            (int) $employee['id'], $data['from_date'], $data['to_date'], $isHalfDay, $data['half_day_session'] ?? null, $policyRule
        );
        if ($calc['totalCountableDays'] <= 0) {
            throw new RuntimeException('Selected dates contain no working leave days.');
        }

        if ($this->applications->hasOverlap((int) $employee['id'], $data['from_date'], $data['to_date'])) {
            throw new RuntimeException('This overlaps an existing pending or approved leave application.');
        }

        if ($calc['totalCountableDays'] < (float) $rule['min_days_per_application']) {
            throw new RuntimeException(sprintf('This leave type requires a minimum of %.1f day(s) per application.', $rule['min_days_per_application']));
        }

        $maxConsecutive = $rule['max_consecutive_days'] ?? $settings['max_consecutive_leave'];
        if ($maxConsecutive !== null && $calc['totalCountableDays'] > $maxConsecutive) {
            throw new RuntimeException(sprintf('Maximum consecutive leave for this type is %d day(s).', $maxConsecutive));
        }

        $financialYear = leave_financial_year($data['from_date']);
        if ($rule['max_applications_per_year'] !== null) {
            [$fyStart, $fyEnd] = leave_financial_year_bounds($financialYear);
            if ($this->applications->countForYear((int) $employee['id'], (int) $leaveType['id'], $fyStart, $fyEnd) >= $rule['max_applications_per_year']) {
                throw new RuntimeException('Maximum number of applications for this leave type has been reached for this year.');
            }
        }

        $noticeDays = $rule['notice_period_days'] ?? $settings['min_notice_days'];
        if ($noticeDays > 0 && empty($data['is_emergency'])) {
            $daysUntil = (int) round((strtotime($data['from_date']) - strtotime(date('Y-m-d'))) / 86400);
            if ($daysUntil < $noticeDays) {
                throw new RuntimeException(sprintf('This leave type requires at least %d day(s) notice.', $noticeDays));
            }
        }

        if ($leaveType['attachment_required'] && empty($data['attachment_path'])) {
            throw new RuntimeException('An attachment is required for this leave type.');
        }
        if ($leaveType['medical_certificate_required'] && empty($data['attachment_path'])) {
            throw new RuntimeException('A medical certificate is required for this leave type.');
        }

        // $rule (not the possibly-null $policyRule) so a type with no explicit policy-rule row still
        // gets its own annual_allocation credited on first balance-row creation, not zero.
        $this->balanceService->assertSufficient($leaveType, (int) $employee['id'], (int) $leaveType['id'], $calc['totalCountableDays'], $financialYear, $rule, $settings);

        $level = $this->approvals->resolveInitialLevel($employee);

        $applicationData = [
            'employee_id'             => $employee['id'],
            'leave_type_id'           => $leaveType['id'],
            'leave_policy_id'         => $policy['id'] ?? null,
            'from_date'               => $data['from_date'],
            'to_date'                 => $data['to_date'],
            'is_half_day'             => $isHalfDay ? 1 : 0,
            'half_day_session'        => $isHalfDay ? $data['half_day_session'] : null,
            'total_days'              => $calc['totalCountableDays'],
            'reason'                  => $data['reason'],
            'emergency_contact_name'  => $data['emergency_contact_name'] ?? null,
            'emergency_contact_phone' => $data['emergency_contact_phone'] ?? null,
            'attachment_path'         => $data['attachment_path'] ?? null,
            'is_emergency'            => ! empty($data['is_emergency']) ? 1 : 0,
            'status'                  => 'pending',
            'current_level'           => $level['current_level'],
            'level1_approver_id'      => $level['level1_approver_id'],
            'level1_status'           => $level['level1_status'],
            'submitted_at'            => date('Y-m-d H:i:s'),
            'created_by'              => $data['created_by'] ?? session('tenant_user_id'),
        ];

        $id = $this->applications->insert($applicationData, true);

        $dayModel = new LeaveApplicationDayModel($db);
        $rows     = array_map(static fn (array $d) => $d + ['leave_application_id' => $id], $calc['days']);
        $dayModel->insertBatch($rows);

        if (! empty($data['delegate_employee_id'])) {
            (new LeaveDelegationModel($db))->insert([
                'leave_application_id' => $id, 'delegate_employee_id' => $data['delegate_employee_id'],
                'from_date' => $data['from_date'], 'to_date' => $data['to_date'],
                'notes' => $data['delegation_notes'] ?? null, 'created_by' => session('tenant_user_id'),
            ]);
            $this->logHistory($id, 'delegation_assigned', 'system', (int) session('tenant_user_id'), null, 'pending');
        }

        $this->logHistory($id, 'submitted', null, (int) session('tenant_user_id'), null, 'pending');
        $this->audit->log('leave_apply', 'leave', 'leave_application', $id, null, $applicationData, (int) $employee['id']);

        if (! empty($level['level1_approver_id'])) {
            $this->notifications->leaveApplied(
                (int) $level['level1_approver_id'],
                trim($employee['first_name'] . ' ' . $employee['last_name']),
                $leaveType['name'],
                $data['from_date'],
                $data['to_date']
            );
        }

        return $id;
    }

    public function cancel(int $id, int $actingUserId, string $reason): void
    {
        $app = $this->applications->find($id);
        if (! $app) {
            throw new RuntimeException('Leave application not found.');
        }
        if (! in_array($app['status'], ['pending', 'approved'], true)) {
            throw new RuntimeException('Only a pending or approved application can be cancelled.');
        }

        $wasApproved = $app['status'] === 'approved';

        $this->applications->update($id, [
            'status' => 'cancelled', 'cancelled_by' => $actingUserId, 'cancelled_at' => date('Y-m-d H:i:s'),
            'cancellation_reason' => $reason,
        ]);

        if ($wasApproved) {
            $this->balanceService->restoreOnCancellation($id);
            $this->attendance->rollbackFromAttendance($id);
        }

        $this->logHistory($id, 'cancelled', null, $actingUserId, $reason, 'cancelled');
        $this->audit->log('leave_cancel', 'leave', 'leave_application', $id, $app, ['status' => 'cancelled'], (int) $app['employee_id']);
    }

    /**
     * A leave type without an explicit policy-rule row is still usable —
     * falls back to the type's own master-data defaults rather than an
     * effective allocation of zero. Numeric fields left null here mean
     * "inherit the leave_settings global", exactly as a real policy rule's
     * null fields do.
     */
    private function effectiveRule(array $leaveType, ?array $policyRule): array
    {
        if ($policyRule !== null) {
            return $policyRule;
        }

        return [
            'annual_allocation' => $leaveType['annual_allocation'], 'accrual_method' => 'annual', 'monthly_accrual_days' => 0,
            'carry_forward_allowed' => $leaveType['carry_forward_allowed'], 'carry_forward_limit' => null, 'carry_forward_unlimited' => 0,
            'encashment_allowed' => $leaveType['encashment_allowed'], 'max_consecutive_days' => null,
            'min_days_per_application' => 0.5, 'max_applications_per_year' => null, 'sandwich_rule_applicable' => 1,
            'notice_period_days' => null,
        ];
    }

    private function logHistory(int $applicationId, string $action, ?string $level, int $actorId, ?string $remarks, string $snapshotStatus): void
    {
        $roles = (new UserModel(service('tenantContext')->db()))->roleSlugs($actorId);
        (new LeaveApprovalHistoryModel(service('tenantContext')->db()))->insert([
            'leave_application_id' => $applicationId, 'action' => $action, 'level' => $level, 'actor_id' => $actorId,
            'actor_role' => $roles[0] ?? null, 'remarks' => $remarks, 'override_reason' => null,
            'snapshot_status' => $snapshotStatus, 'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
