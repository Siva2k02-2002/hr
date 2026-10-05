<?php

namespace App\Services;

use App\Models\AttendanceLogModel;
use App\Models\AttendanceModel;
use App\Models\AttendanceRegularizationModel;
use App\Models\EmployeeModel;
use App\Models\UserModel;
use RuntimeException;

/** Request -> Manager/HR approval -> attendance updated, exactly the workflow the spec describes. */
class AttendanceRegularizationService
{
    private AttendanceRegularizationModel $regularizations;

    public function __construct(
        private AuditService $audit = new AuditService(),
        private AttendanceSummaryService $summary = new AttendanceSummaryService(),
        private AttendanceShiftAssignmentService $shiftAssignments = new AttendanceShiftAssignmentService(),
        private NotificationService $notifications = new NotificationService()
    ) {
        $this->regularizations = new AttendanceRegularizationModel(service('tenantContext')->db());
    }

    public function request(array $data): int
    {
        $data['status'] = 'pending';
        $id             = $this->regularizations->insert($data);
        $this->audit->log('regularization_request', 'attendance', 'attendance_regularization', $id, null, $data, (int) $data['employee_id']);

        return $id;
    }

    public function approve(int $id, int $reviewerId, ?string $remarks = null): void
    {
        $reg = $this->assertPending($id);
        $this->assertCanReview($reg, $reviewerId);
        $db  = service('tenantContext')->db();

        // Same overnight-shift rule AttendanceSummaryService uses when reading
        // logs back (forShiftDate): a shift whose end_time doesn't come after
        // its start_time wraps past midnight, so a punch-out lands on the
        // calendar day *after* attendance_date, not on attendance_date itself.
        $shift            = $this->shiftAssignments->resolveShiftFor((int) $reg['employee_id'], $reg['attendance_date']);
        $isOvernightShift = $shift && strtotime($shift['end_time']) <= strtotime($shift['start_time']);

        $db->transStart();

        if ($reg['requested_punch_in']) {
            $this->insertCorrectionLog($db, (int) $reg['employee_id'], $reg['attendance_date'], 'in', $reg['requested_punch_in']);
        }
        if ($reg['requested_punch_out']) {
            $punchOutDate = $isOvernightShift
                ? date('Y-m-d', strtotime($reg['attendance_date'] . ' +1 day'))
                : $reg['attendance_date'];
            $this->insertCorrectionLog($db, (int) $reg['employee_id'], $punchOutDate, 'out', $reg['requested_punch_out']);
        }

        $attendance = $this->summary->recompute((int) $reg['employee_id'], $reg['attendance_date']);
        (new AttendanceModel($db))->update($attendance['id'], ['source' => 'regularized']);

        $this->regularizations->update($id, [
            'status' => 'approved', 'reviewed_by' => $reviewerId, 'reviewed_at' => date('Y-m-d H:i:s'), 'review_remarks' => $remarks,
        ]);

        $db->transComplete();
        if ($db->transStatus() === false) {
            throw new RuntimeException('Could not apply the regularization correction. Please try again.');
        }

        $this->audit->log('regularization_approve', 'attendance', 'attendance_regularization', $id, $reg, ['status' => 'approved'], (int) $reg['employee_id']);

        $employee = (new EmployeeModel($db))->find((int) $reg['employee_id']);
        if (! empty($employee['user_id'])) {
            $this->notifications->attendanceRegularizationApproved((int) $employee['user_id'], $reg['attendance_date']);
        }
    }

    public function reject(int $id, int $reviewerId, ?string $remarks = null): void
    {
        $reg = $this->assertPending($id);
        $this->assertCanReview($reg, $reviewerId);

        $this->regularizations->update($id, [
            'status' => 'rejected', 'reviewed_by' => $reviewerId, 'reviewed_at' => date('Y-m-d H:i:s'), 'review_remarks' => $remarks,
        ]);

        $this->audit->log('regularization_reject', 'attendance', 'attendance_regularization', $id, $reg, ['status' => 'rejected'], (int) $reg['employee_id']);
    }

    /**
     * `attendance.regularization.approve` is granted company-wide to every Manager and
     * HR Manager, so the route permission alone doesn't stop a manager from reviewing a
     * request that isn't one of their own reports' — HR Manager/Company Admin act company-
     * wide by design (mirrors leave's level2 tier), but a plain Manager may only review
     * requests from employees who currently report to them.
     */
    private function assertCanReview(array $reg, int $reviewerId): void
    {
        $roles = (new UserModel(service('tenantContext')->db()))->roleSlugs($reviewerId);
        if (array_intersect($roles, ['hr-manager', 'company-admin']) !== []) {
            return;
        }

        $employee = (new EmployeeModel(service('tenantContext')->db()))->find((int) $reg['employee_id']);
        $manager  = $employee && $employee['reporting_manager_id']
            ? (new EmployeeModel(service('tenantContext')->db()))->find($employee['reporting_manager_id'])
            : null;

        if ((int) ($manager['user_id'] ?? 0) !== $reviewerId) {
            throw new RuntimeException('You are not authorized to review this request.');
        }
    }

    private function assertPending(int $id): array
    {
        $reg = $this->regularizations->find($id);
        if (! $reg) {
            throw new RuntimeException('Regularization request not found.');
        }
        if ($reg['status'] !== 'pending') {
            throw new RuntimeException('This request has already been reviewed.');
        }

        return $reg;
    }

    private function insertCorrectionLog($db, int $employeeId, string $date, string $type, string $time): void
    {
        $punchTime = $date . ' ' . $time;

        $exists = (new AttendanceLogModel($db))
            ->where('employee_id', $employeeId)->where('punch_type', $type)->where('punch_time', $punchTime)->first();
        if ($exists) {
            return; // re-approval retry — correction already applied
        }

        (new AttendanceLogModel($db))->insert([
            'employee_id' => $employeeId, 'punch_type' => $type, 'punch_time' => $punchTime,
            'geofence_status' => 'not_applicable', 'source' => 'manual', 'remarks' => 'Regularization correction',
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
