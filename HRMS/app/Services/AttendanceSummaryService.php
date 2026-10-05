<?php

namespace App\Services;

use App\Models\AttendanceHolidayModel;
use App\Models\AttendanceLogModel;
use App\Models\AttendanceModel;
use App\Models\AttendanceOvertimeModel;
use App\Models\AttendanceSettingModel;
use App\Models\EmployeeModel;

/**
 * The one place late/early-exit/overtime/status math happens — called after
 * every punch and after every manual/regularization change, so the daily
 * aggregate (`attendance` table) is always *derived* from its logs, never
 * hand-edited independently of them.
 */
class AttendanceSummaryService
{
    public function __construct(
        private AttendanceShiftAssignmentService $shiftAssignments = new AttendanceShiftAssignmentService(),
        private AttendanceWeeklyOffService $weeklyOffs = new AttendanceWeeklyOffService()
    ) {
    }

    public function recompute(int $employeeId, string $date): array
    {
        $db       = service('tenantContext')->db();
        $employee = (new EmployeeModel($db))->find($employeeId);
        $settings = (new AttendanceSettingModel($db))->current();
        $shift    = $this->shiftAssignments->resolveShiftFor($employeeId, $date);

        // A shift whose end_time is not after its start_time wraps past midnight
        // (e.g. 22:00 -> 06:00) — its punch-out lands on the next calendar day, so
        // logs for this shift date must be gathered from a noon-to-noon window
        // rather than a single calendar day.
        $crossesMidnight = $shift && strtotime($shift['end_time']) <= strtotime($shift['start_time']);
        $logModel         = new AttendanceLogModel($db);
        $logs             = $crossesMidnight ? $logModel->forShiftDate($employeeId, $date) : $logModel->forEmployeeAndDate($employeeId, $date);

        $holiday   = (new AttendanceHolidayModel($db))->forDate($date, $employee['branch_id'] ?? null);
        $weeklyOff = $this->weeklyOffs->isWeeklyOff($employee['branch_id'] ?? null, $date, $shift['id'] ?? null);

        [$firstIn, $lastOut, $workingMinutes, $openIn] = $this->summarizeLogs($logs);

        $shiftMinutes     = 0;
        $lateMinutes      = 0;
        $earlyExitMinutes = 0;
        $overtimeMinutes  = 0;

        if ($shift) {
            $dayPrefix  = date('Y-m-d', strtotime($date)) . ' ';
            $shiftStart = strtotime($dayPrefix . $shift['start_time']);
            $shiftEnd   = strtotime($dayPrefix . $shift['end_time']);
            if ($shiftEnd <= $shiftStart) {
                $shiftEnd = strtotime('+1 day', $shiftEnd); // night shift crossing midnight
            }
            $shiftMinutes = (int) round(($shiftEnd - $shiftStart) / 60);

            if ($firstIn) {
                $graceSeconds = ((int) ($settings['grace_minutes'] ?? 0)) * 60;
                $lateMinutes  = max(0, (int) round((strtotime($firstIn) - $shiftStart - $graceSeconds) / 60));
            }

            if ($lastOut && strtotime($lastOut) < $shiftEnd) {
                $earlyExitMinutes = max(0, (int) round(($shiftEnd - strtotime($lastOut)) / 60));
            }

            if (! empty($settings['overtime_enabled']) && $workingMinutes > $shiftMinutes) {
                $overtimeMinutes = $workingMinutes - $shiftMinutes;
            }
        }

        $status = $this->resolveStatus($holiday, $weeklyOff, $logs, $openIn, $workingMinutes, $settings, $lateMinutes);

        $data = [
            'employee_id'        => $employeeId,
            'attendance_date'    => $date,
            'shift_id'           => $shift['id'] ?? null,
            'first_punch_in_at'  => $firstIn,
            'last_punch_out_at'  => $lastOut,
            'working_minutes'    => $workingMinutes,
            'break_minutes'      => 0,
            'late_minutes'       => $lateMinutes,
            'early_exit_minutes' => $earlyExitMinutes,
            'overtime_minutes'   => $overtimeMinutes,
            'status'             => $status,
            'source'             => $logs !== [] ? ($logs[0]['source'] === 'biometric' ? 'biometric' : 'gps') : 'manual',
        ];

        $attendanceModel = new AttendanceModel($db);
        $existing        = $attendanceModel->forEmployeeAndDate($employeeId, $date);
        $attendanceId    = $existing ? $existing['id'] : null;

        if ($existing) {
            $attendanceModel->update($existing['id'], $data);
        } else {
            $attendanceId = $attendanceModel->insert($data, true);
        }

        (new AttendanceLogModel($db))
            ->where('employee_id', $employeeId)
            ->where('DATE(punch_time)', $date)
            ->set(['attendance_id' => $attendanceId])
            ->update();

        $this->recordOvertime($db, $employeeId, $date, $shiftMinutes, $workingMinutes, $overtimeMinutes);

        return $attendanceModel->find($attendanceId);
    }

    /** @return array{0:?string,1:?string,2:int,3:?string} first-in, last-out, working minutes, still-open-punch-in time */
    private function summarizeLogs(array $logs): array
    {
        $firstIn        = null;
        $lastOut        = null;
        $workingMinutes = 0;
        $openIn         = null;

        foreach ($logs as $log) {
            if ($log['punch_type'] === 'in') {
                $firstIn ??= $log['punch_time'];
                $openIn = $log['punch_time'];
            } elseif ($log['punch_type'] === 'out' && $openIn !== null) {
                $workingMinutes += (int) round((strtotime($log['punch_time']) - strtotime($openIn)) / 60);
                $lastOut = $log['punch_time'];
                $openIn  = null;
            }
        }

        return [$firstIn, $lastOut, $workingMinutes, $openIn];
    }

    private function resolveStatus(?array $holiday, bool $weeklyOff, array $logs, ?string $openIn, int $workingMinutes, array $settings, int $lateMinutes): string
    {
        if ($logs === []) {
            if ($holiday) {
                return 'holiday';
            }
            if ($weeklyOff) {
                return 'weekly_off';
            }

            return 'absent';
        }

        if ($openIn !== null) {
            return 'missed_punch';
        }

        if ($workingMinutes >= (int) $settings['half_day_minutes'] && $workingMinutes < (int) $settings['full_day_minutes']) {
            return 'half_day';
        }

        // late_mark_minutes is the company's own tolerance beyond grace_minutes before a
        // late arrival actually counts as a 'late' attendance status — was stored and
        // editable in Settings but never read, so every minute past grace was marked late.
        return $lateMinutes >= (int) ($settings['late_mark_minutes'] ?? 0) && $lateMinutes > 0 ? 'late' : 'present';
    }

    private function recordOvertime($db, int $employeeId, string $date, int $shiftMinutes, int $workingMinutes, int $overtimeMinutes): void
    {
        if ($overtimeMinutes <= 0) {
            return;
        }

        $overtimeModel = new AttendanceOvertimeModel($db);
        $existing      = $overtimeModel->forEmployeeAndDate($employeeId, $date);
        $data          = [
            'employee_id' => $employeeId, 'attendance_date' => $date,
            'shift_minutes' => $shiftMinutes, 'worked_minutes' => $workingMinutes, 'overtime_minutes' => $overtimeMinutes,
        ];

        $existing ? $overtimeModel->update($existing['id'], $data) : $overtimeModel->insert($data);
    }
}
