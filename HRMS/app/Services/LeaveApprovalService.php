<?php

namespace App\Services;

use App\Models\EmployeeModel;
use App\Models\LeaveApplicationModel;
use App\Models\LeaveApprovalHistoryModel;
use App\Models\LeaveSettingModel;
use App\Models\UserModel;
use RuntimeException;

/**
 * Owns the fixed 2-level approval chain (Reporting Manager -> HR Manager) and
 * its self-approval guard. Company Admin can step in via overrideApprove/
 * overrideReject instead of a dedicated N-level chain table — see the Phase
 * 6 plan for why.
 */
class LeaveApprovalService
{
    private LeaveApplicationModel $applications;

    public function __construct(
        private AuditService $audit = new AuditService(),
        private LeaveBalanceService $balances = new LeaveBalanceService(),
        private LeaveAttendanceIntegrationService $attendance = new LeaveAttendanceIntegrationService(),
        private NotificationService $notifications = new NotificationService()
    ) {
        $this->applications = new LeaveApplicationModel(service('tenantContext')->db());
    }

    /** Called from every method that reaches a final approved/rejected outcome — see call sites below. */
    private function notifyOutcome(array $app, bool $approved, ?string $remarks = null): void
    {
        $employee = (new EmployeeModel(service('tenantContext')->db()))->find($app['employee_id']);
        if (empty($employee['user_id'])) {
            return;
        }

        $leaveType = service('tenantContext')->db()->table('leave_types')->select('name')->where('id', $app['leave_type_id'])->get()->getRowArray();
        $typeName  = $leaveType['name'] ?? 'Leave';

        if ($approved) {
            $this->notifications->leaveApproved((int) $employee['user_id'], $typeName, $app['from_date'], $app['to_date']);
        } else {
            $this->notifications->leaveRejected((int) $employee['user_id'], $typeName, $app['from_date'], $app['to_date'], $remarks);
        }
    }

    /** Called by LeaveApplicationService::submit() to seed level1/level2 fields on a new application. */
    public function resolveInitialLevel(array $employee): array
    {
        $manager = null;
        if (! empty($employee['reporting_manager_id'])) {
            $manager = (new EmployeeModel(service('tenantContext')->db()))->find($employee['reporting_manager_id']);
        }

        if (empty($manager['user_id'] ?? null)) {
            return ['level1_approver_id' => null, 'level1_status' => 'skipped', 'current_level' => 'level2'];
        }

        return ['level1_approver_id' => (int) $manager['user_id'], 'level1_status' => 'pending', 'current_level' => 'level1'];
    }

    public function approveLevel1(int $id, int $actorId, ?string $remarks = null): void
    {
        $app = $this->assertActionable($id, 'level1');
        $this->assertNotSelf($app, $actorId);
        $this->assertIsLevel1Approver($app, $actorId);

        $this->applications->update($id, [
            'level1_status' => 'approved', 'level1_acted_by' => $actorId, 'level1_acted_at' => date('Y-m-d H:i:s'),
            'level1_remarks' => $remarks, 'current_level' => 'level2',
        ]);
        $this->logHistory($id, 'level1_approved', 'level1', $actorId, $remarks, 'pending');
        $this->audit->log('leave_approve_level1', 'leave', 'leave_application', $id, $app, ['level1_status' => 'approved'], (int) $app['employee_id']);
    }

    public function rejectLevel1(int $id, int $actorId, ?string $remarks = null): void
    {
        $app = $this->assertActionable($id, 'level1');
        $this->assertNotSelf($app, $actorId);
        $this->assertIsLevel1Approver($app, $actorId);

        $this->applications->update($id, [
            'status' => 'rejected', 'level1_status' => 'rejected', 'level1_acted_by' => $actorId,
            'level1_acted_at' => date('Y-m-d H:i:s'), 'level1_remarks' => $remarks,
        ]);
        $this->logHistory($id, 'level1_rejected', 'level1', $actorId, $remarks, 'rejected');
        $this->audit->log('leave_reject_level1', 'leave', 'leave_application', $id, $app, ['status' => 'rejected'], (int) $app['employee_id']);
        $this->notifyOutcome($app, approved: false, remarks: $remarks);
    }

    public function approveLevel2(int $id, int $actorId, ?string $remarks = null): void
    {
        $app = $this->assertActionable($id, 'level2');
        $this->assertNotSelf($app, $actorId);
        $this->assertLevel2Role($actorId);

        $this->applications->update($id, [
            'status' => 'approved', 'level2_status' => 'approved', 'level2_acted_by' => $actorId,
            'level2_acted_at' => date('Y-m-d H:i:s'), 'level2_remarks' => $remarks, 'current_level' => 'completed',
        ]);
        $this->logHistory($id, 'level2_approved', 'level2', $actorId, $remarks, 'approved');

        $this->balances->deductOnApproval($id);
        $this->attendance->applyToAttendance($id);

        $this->audit->log('leave_approve_level2', 'leave', 'leave_application', $id, $app, ['status' => 'approved'], (int) $app['employee_id']);
        $this->notifyOutcome($app, approved: true);
    }

    public function rejectLevel2(int $id, int $actorId, ?string $remarks = null): void
    {
        $app = $this->assertActionable($id, 'level2');
        $this->assertNotSelf($app, $actorId);
        $this->assertLevel2Role($actorId);

        $this->applications->update($id, [
            'status' => 'rejected', 'level2_status' => 'rejected', 'level2_acted_by' => $actorId,
            'level2_acted_at' => date('Y-m-d H:i:s'), 'level2_remarks' => $remarks,
        ]);
        $this->logHistory($id, 'level2_rejected', 'level2', $actorId, $remarks, 'rejected');
        $this->audit->log('leave_reject_level2', 'leave', 'leave_application', $id, $app, ['status' => 'rejected'], (int) $app['employee_id']);
        $this->notifyOutcome($app, approved: false, remarks: $remarks);
    }

    public function overrideApprove(int $id, int $actorId, string $reason): void
    {
        $app = $this->applications->find($id);
        if (! $app) {
            throw new RuntimeException('Leave application not found.');
        }
        if ($app['status'] !== 'pending') {
            throw new RuntimeException('Only a pending application can be override-approved.');
        }
        $this->assertOverrideAllowed($app, $actorId, $reason);

        $this->applications->update($id, [
            'status'         => 'approved',
            'level1_status'  => $app['level1_status'] === 'pending' ? 'approved' : $app['level1_status'],
            'level2_status'  => 'approved',
            'level2_acted_by' => $actorId, 'level2_acted_at' => date('Y-m-d H:i:s'), 'level2_remarks' => $reason,
            'current_level'  => 'completed',
        ]);
        $this->logHistory($id, 'admin_override_approved', 'admin', $actorId, null, 'approved', $reason);

        $this->balances->deductOnApproval($id);
        $this->attendance->applyToAttendance($id);

        $this->audit->log('leave_override_approve', 'leave', 'leave_application', $id, $app, ['status' => 'approved', 'override_reason' => $reason], (int) $app['employee_id']);
        $this->notifyOutcome($app, approved: true);
    }

    public function overrideReject(int $id, int $actorId, string $reason): void
    {
        $app = $this->applications->find($id);
        if (! $app) {
            throw new RuntimeException('Leave application not found.');
        }
        if ($app['status'] !== 'pending') {
            throw new RuntimeException('Only a pending application can be override-rejected.');
        }
        $this->assertOverrideAllowed($app, $actorId, $reason);

        $this->applications->update($id, [
            'status'          => 'rejected',
            'level1_status'   => $app['level1_status'] === 'pending' ? 'rejected' : $app['level1_status'],
            'level2_status'   => 'rejected',
            'level2_acted_by' => $actorId, 'level2_acted_at' => date('Y-m-d H:i:s'), 'level2_remarks' => $reason,
        ]);
        $this->logHistory($id, 'admin_override_rejected', 'admin', $actorId, null, 'rejected', $reason);

        $this->audit->log('leave_override_reject', 'leave', 'leave_application', $id, $app, ['status' => 'rejected', 'override_reason' => $reason], (int) $app['employee_id']);
        $this->notifyOutcome($app, approved: false, remarks: $reason);
    }

    private function assertActionable(int $id, string $expectedLevel): array
    {
        $app = $this->applications->find($id);
        if (! $app) {
            throw new RuntimeException('Leave application not found.');
        }
        if ($app['status'] !== 'pending') {
            throw new RuntimeException('This application has already been reviewed.');
        }
        if ($app['current_level'] !== $expectedLevel) {
            throw new RuntimeException('This application is not currently at that approval level.');
        }

        return $app;
    }

    /**
     * `leave.approve` is granted company-wide (any Manager/HR Manager holds it), so the route
     * permission alone doesn't stop a manager from acting on an application that isn't theirs
     * to decide — only the assigned level1_approver_id (the applicant's actual reporting
     * manager, set by resolveInitialLevel()) may act at level1. Company Admin's only path
     * around this is overrideApprove/overrideReject, same as the self-approval rule below.
     */
    private function assertIsLevel1Approver(array $app, int $actorId): void
    {
        if ((int) ($app['level1_approver_id'] ?? 0) !== $actorId) {
            throw new RuntimeException('You are not the assigned approver for this application.');
        }
    }

    /** No exception, ever, including company-admin — see the Phase 6 plan's self-approval rule. The only path for self-action is overrideApprove/overrideReject. */
    private function assertNotSelf(array $app, int $actorId): void
    {
        $applicant = (new EmployeeModel(service('tenantContext')->db()))->find($app['employee_id']);
        if ((int) ($applicant['user_id'] ?? 0) === $actorId) {
            throw new RuntimeException('You cannot approve or reject your own leave application.');
        }
    }

    /** leave.approve is granted to both manager (level1 duty) and hr-manager (level2 duty) — the route permission alone can't tell levels apart. */
    private function assertLevel2Role(int $actorId): void
    {
        $roles = (new UserModel(service('tenantContext')->db()))->roleSlugs($actorId);
        if (array_intersect($roles, ['hr-manager', 'company-admin']) === []) {
            throw new RuntimeException('Only HR Manager or Company Admin can act at this approval level.');
        }
    }

    private function assertOverrideAllowed(array $app, int $actorId, string $reason): void
    {
        $roles = (new UserModel(service('tenantContext')->db()))->roleSlugs($actorId);
        if (! in_array('company-admin', $roles, true)) {
            throw new RuntimeException('Only Company Admin can override an approval decision.');
        }
        if (trim($reason) === '') {
            throw new RuntimeException('An override reason is required.');
        }

        $applicant = (new EmployeeModel(service('tenantContext')->db()))->find($app['employee_id']);
        if ((int) ($applicant['user_id'] ?? 0) === $actorId) {
            $settings = (new LeaveSettingModel(service('tenantContext')->db()))->current();
            if (! $settings['self_approval_allowed_for_admin']) {
                throw new RuntimeException('Self-approval override is disabled in leave settings.');
            }
        }
    }

    private function logHistory(int $applicationId, string $action, ?string $level, int $actorId, ?string $remarks, string $snapshotStatus, ?string $overrideReason = null): void
    {
        $actorRoles = (new UserModel(service('tenantContext')->db()))->roleSlugs($actorId);
        (new LeaveApprovalHistoryModel(service('tenantContext')->db()))->insert([
            'leave_application_id' => $applicationId, 'action' => $action, 'level' => $level, 'actor_id' => $actorId,
            'actor_role' => $actorRoles[0] ?? null, 'remarks' => $remarks, 'override_reason' => $overrideReason,
            'snapshot_status' => $snapshotStatus, 'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
