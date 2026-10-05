<?php

namespace App\Controllers;

use App\Models\AttendanceHolidayModel;
use App\Models\AttendanceModel;
use App\Models\EmployeeModel;
use App\Services\AttendanceShiftAssignmentService;
use App\Services\AttendanceWeeklyOffService;

class AttendanceDashboardController extends BaseController
{
    public function index()
    {
        $db    = service('tenantContext')->db();
        $today = date('Y-m-d');

        $employees   = (new EmployeeModel($db))->select('id, branch_id')->where('status !=', 'terminated')->findAll();
        $activeIds   = array_column($employees, 'id');

        $todayRows = $activeIds !== []
            ? (new AttendanceModel($db))->whereIn('employee_id', $activeIds)->where('attendance_date', $today)->findAll()
            : [];

        $presentCount   = 0;
        $lateCount      = 0;
        $weeklyOffCount = 0;
        $holidayCount   = 0;
        $onLeaveCount   = 0;
        $recordedIds    = [];

        foreach ($todayRows as $row) {
            $recordedIds[] = (int) $row['employee_id'];
            if (in_array($row['status'], ['present', 'late', 'half_day', 'work_from_home', 'on_duty'], true)) {
                $presentCount++;
            }
            match ($row['status']) {
                'late'                       => $lateCount++,
                'weekly_off'                 => $weeklyOffCount++,
                'holiday'                    => $holidayCount++,
                'leave', 'half_day_leave'    => $onLeaveCount++,
                default                      => null,
            };
        }

        // Absent-by-omission (no cron): active employees with no row yet today, unless it's their holiday/weekly-off.
        $holidayModel     = new AttendanceHolidayModel($db);
        $weeklyOffService = new AttendanceWeeklyOffService();
        $shiftAssignments = new AttendanceShiftAssignmentService();
        $absentCount      = 0;

        foreach ($employees as $e) {
            if (in_array((int) $e['id'], $recordedIds, true)) {
                continue;
            }
            if ($holidayModel->forDate($today, $e['branch_id'])) {
                $holidayCount++;
                continue;
            }
            $shift = $shiftAssignments->resolveShiftFor((int) $e['id'], $today);
            if ($weeklyOffService->isWeeklyOff($e['branch_id'], $today, $shift['id'] ?? null)) {
                $weeklyOffCount++;
                continue;
            }
            $absentCount++;
        }

        return view('attendance/dashboard', [
            'title'                  => 'Attendance Dashboard',
            'presentCount'           => $presentCount,
            'absentCount'            => $absentCount,
            'lateCount'              => $lateCount,
            'onLeaveCount'           => $onLeaveCount,
            'weeklyOffCount'         => $weeklyOffCount,
            'holidaysThisMonthCount' => count($holidayModel->forMonth((int) date('Y'), (int) date('n'))),
            'totalActive'            => count($employees),
        ]);
    }
}
