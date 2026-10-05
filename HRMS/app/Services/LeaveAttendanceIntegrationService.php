<?php

namespace App\Services;

use App\Models\AttendanceModel;
use App\Models\LeaveApplicationDayModel;
use App\Models\LeaveApplicationModel;
use App\Models\LeaveTypeModel;

/**
 * Writes/rolls back the `attendance` rows an approved leave application
 * covers, reusing AttendanceModel::forEmployeeAndDate()'s find-or-insert
 * idiom exactly as AttendanceSummaryService/AttendanceRegularizationService
 * already do — no new attendance-writing code path is introduced.
 */
class LeaveAttendanceIntegrationService
{
    public function __construct(private AttendanceSummaryService $summary = new AttendanceSummaryService())
    {
    }

    public function applyToAttendance(int $applicationId): void
    {
        $db           = service('tenantContext')->db();
        $applications = new LeaveApplicationModel($db);
        $app          = $applications->find($applicationId);
        if (! $app || (int) $app['attendance_applied'] === 1) {
            return;
        }

        $leaveType       = (new LeaveTypeModel($db))->find((int) $app['leave_type_id']);
        $days            = (new LeaveApplicationDayModel($db))->forApplication($applicationId);
        $attendanceModel = new AttendanceModel($db);

        foreach ($days as $day) {
            if (! $day['counts_as_leave']) {
                continue;
            }

            $status = $day['day_type'] !== 'full' ? 'half_day_leave' : $leaveType['attendance_status_map'];
            $data   = [
                'employee_id'     => $app['employee_id'],
                'attendance_date' => $day['leave_date'],
                'status'          => $status,
                'source'          => 'leave',
            ];

            $existing = $attendanceModel->forEmployeeAndDate((int) $app['employee_id'], $day['leave_date']);
            $existing ? $attendanceModel->update($existing['id'], $data) : $attendanceModel->insert($data, true);
        }

        $applications->update($applicationId, ['attendance_applied' => 1]);
    }

    /** Only touches an attendance row if source==='leave' — never clobbers a manual correction HR made since. */
    public function rollbackFromAttendance(int $applicationId): void
    {
        $db           = service('tenantContext')->db();
        $applications = new LeaveApplicationModel($db);
        $app          = $applications->find($applicationId);
        if (! $app || (int) $app['attendance_applied'] !== 1) {
            return;
        }

        $days            = (new LeaveApplicationDayModel($db))->forApplication($applicationId);
        $attendanceModel = new AttendanceModel($db);

        foreach ($days as $day) {
            if (! $day['counts_as_leave']) {
                continue;
            }

            $existing = $attendanceModel->forEmployeeAndDate((int) $app['employee_id'], $day['leave_date']);
            if (! $existing || $existing['source'] !== 'leave') {
                continue;
            }

            $this->summary->recompute((int) $app['employee_id'], $day['leave_date']);
        }

        $applications->update($applicationId, ['attendance_applied' => 0]);
    }
}
