<?php

namespace App\Services;

use App\Models\AttendanceModel;
use App\Models\AttendanceOvertimeModel;

/**
 * Read-only bridge into Phase 5/6 attendance data — never writes to
 * `attendance`, never recomputes what AttendanceSummaryService already
 * decided. Every attendance row in the period is bucketed into
 * present/paid-leave/LOP exactly once (holiday/weekly_off days are excluded
 * from the working-day denominator entirely, not bucketed):
 *
 *   present, late, missed_punch, work_from_home, on_duty -> 1.0 present
 *   leave                                                 -> 1.0 paid leave
 *   half_day_leave                                        -> 0.5 present + 0.5 paid leave
 *   half_day                                               -> 0.5 present + 0.5 LOP (worked partial day, nothing covers the rest)
 *   lop, absent                                            -> 1.0 LOP
 *
 * half_day_leave carries no record of whether the underlying leave type was
 * paid or LOP (LeaveAttendanceIntegrationService collapses every half-day
 * leave to this one status regardless of leave type) — treating it as paid
 * is the documented assumption here, consistent with a half-day leave never
 * being flagged as an unpaid shortfall anywhere else in the UI.
 */
class PayrollAttendanceIntegrationService
{
    public function summarize(int $employeeId, string $startDate, string $endDate, string $workingDaysBasis, int $fixedWorkingDays): array
    {
        $db   = service('tenantContext')->db();
        $rows = (new AttendanceModel($db))
            ->where('employee_id', $employeeId)
            ->where('attendance_date >=', $startDate)
            ->where('attendance_date <=', $endDate)
            ->findAll();

        $present   = 0.0;
        $paidLeave = 0.0;
        $lop       = 0.0;
        $halfDays  = 0.0;
        $absent    = 0.0;
        $lateMarks = 0;
        $nonWorking = 0;

        foreach ($rows as $row) {
            switch ($row['status']) {
                case 'holiday':
                case 'weekly_off':
                    $nonWorking++;
                    break;
                case 'leave':
                    $paidLeave += 1.0;
                    break;
                case 'half_day_leave':
                    $present += 0.5;
                    $paidLeave += 0.5;
                    $halfDays += 1.0;
                    break;
                case 'half_day':
                    $present += 0.5;
                    $lop += 0.5;
                    $halfDays += 1.0;
                    break;
                case 'lop':
                    $lop += 1.0;
                    break;
                case 'absent':
                    $lop += 1.0;
                    $absent += 1.0;
                    break;
                case 'late':
                    $present += 1.0;
                    $lateMarks++;
                    break;
                default: // present, missed_punch, work_from_home, on_duty
                    $present += 1.0;
                    break;
            }
        }

        $totalRows  = count($rows);
        $workingDays = $workingDaysBasis === 'fixed' ? $fixedWorkingDays : max(0, $totalRows - $nonWorking);

        $overtimeMinutes = (new AttendanceOvertimeModel($db))
            ->where('employee_id', $employeeId)
            ->where('attendance_date >=', $startDate)
            ->where('attendance_date <=', $endDate)
            ->where('status', 'approved')
            ->selectSum('overtime_minutes')
            ->first()['overtime_minutes'] ?? 0;

        return [
            'working_days'    => (float) $workingDays,
            'present_days'    => round($present, 1),
            'paid_leave_days' => round($paidLeave, 1),
            'lop_days'        => round($lop, 1),
            'half_days'       => round($halfDays, 1),
            'absent_days'     => round($absent, 1),
            'late_marks'      => $lateMarks,
            'overtime_hours'  => round(((int) $overtimeMinutes) / 60, 2),
        ];
    }
}
