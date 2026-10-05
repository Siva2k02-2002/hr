<?php

namespace App\Services;

use App\Models\AttendanceHolidayModel;
use App\Models\AttendanceModel;
use App\Models\EmployeeModel;
use DateTimeImmutable;

/**
 * Every widget here is optional in the returned array — the view only renders
 * a widget's card when its key is present, and index() only computes a
 * widget's data when the viewer actually holds the permission it depends on.
 * That's what makes this one controller/view double as the Admin, HR,
 * Manager, and Employee dashboard: a plain Employee's `can()` checks are all
 * false for the admin-only widgets, so they simply never appear — same
 * gating pattern already used for the Login tab in employees/form.php.
 */
class DashboardService
{
    public function companyWide(): array
    {
        $db = service('tenantContext')->db();
        $today = date('Y-m-d');

        $employeeCount       = $db->table('employees')->where('deleted_at', null)->countAllResults();
        $activeEmployeeCount = $db->table('employees')->where('deleted_at', null)->where('status', 'active')->countAllResults();

        $attendanceToday = $db->table('attendance')
            ->select('status, COUNT(*) as total')
            ->where('attendance_date', $today)
            ->groupBy('status')
            ->get()->getResultArray();
        $attendanceByStatus = array_column($attendanceToday, 'total', 'status');

        $pendingRegularizations = $db->table('attendance_regularizations')->where('status', 'pending')->countAllResults();
        $pendingLeaves          = $db->table('leave_applications')->where('status', 'pending')->countAllResults();

        $payrollPending = $db->table('payroll_runs')->whereIn('status', ['draft', 'generated'])->countAllResults();

        return [
            'employee_count'          => $employeeCount,
            'active_employee_count'   => $activeEmployeeCount,
            'attendance_today'        => [
                'present'  => (int) ($attendanceByStatus['present'] ?? 0) + (int) ($attendanceByStatus['late'] ?? 0),
                'late'     => (int) ($attendanceByStatus['late'] ?? 0),
                'absent'   => (int) ($attendanceByStatus['absent'] ?? 0),
                'half_day' => (int) ($attendanceByStatus['half_day'] ?? 0),
            ],
            'pending_regularizations' => $pendingRegularizations,
            'pending_leaves'          => $pendingLeaves,
            'payroll_pending_runs'    => $payrollPending,
            'attendance_trend'        => $this->attendanceTrend($db),
            'upcoming_birthdays'      => $this->upcomingBirthdays(),
            'upcoming_holidays'       => $this->upcomingHolidays(),
        ];
    }

    /** @return array<int, array{date:string, present:int}> last 7 calendar days, oldest first */
    private function attendanceTrend($db): array
    {
        $rows = $db->table('attendance')
            ->select('attendance_date, COUNT(*) as total')
            ->where('attendance_date >=', date('Y-m-d', strtotime('-6 days')))
            ->whereIn('status', ['present', 'late', 'half_day'])
            ->groupBy('attendance_date')
            ->get()->getResultArray();
        $byDate = array_column($rows, 'total', 'attendance_date');

        $trend = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $trend[] = ['date' => $date, 'present' => (int) ($byDate[$date] ?? 0)];
        }

        return $trend;
    }

    /** Birthdays in the next 30 days, wrapping the year boundary, sorted by how soon they fall. */
    private function upcomingBirthdays(): array
    {
        $employees = (new EmployeeModel(service('tenantContext')->db()))
            ->select('id, first_name, last_name, date_of_birth')
            ->where('status', 'active')
            ->where('date_of_birth IS NOT NULL')
            ->findAll();

        $today = new DateTimeImmutable('today');
        $upcoming = [];

        foreach ($employees as $e) {
            $dob = DateTimeImmutable::createFromFormat('Y-m-d', $e['date_of_birth']);
            if (! $dob) {
                continue;
            }
            $next = $dob->setDate((int) $today->format('Y'), (int) $dob->format('m'), (int) $dob->format('d'));
            if ($next < $today) {
                $next = $next->setDate((int) $today->format('Y') + 1, (int) $dob->format('m'), (int) $dob->format('d'));
            }
            $daysAway = (int) $today->diff($next)->days;
            if ($daysAway <= 30) {
                $upcoming[] = ['name' => trim($e['first_name'] . ' ' . $e['last_name']), 'date' => $next->format('Y-m-d'), 'days_away' => $daysAway];
            }
        }

        usort($upcoming, static fn ($a, $b) => $a['days_away'] <=> $b['days_away']);

        return array_slice($upcoming, 0, 8);
    }

    private function upcomingHolidays(): array
    {
        $db = service('tenantContext')->db();

        return (new AttendanceHolidayModel($db))
            ->where('status', 'active')
            ->where('date >=', date('Y-m-d'))
            ->where('date <=', date('Y-m-d', strtotime('+30 days')))
            ->orderBy('date')
            ->findAll(8);
    }

    /** @return array{today_status:?string, punched_in:bool, leave_balance:float, pending_regularizations:int} */
    public function forEmployee(int $employeeId): array
    {
        $db    = service('tenantContext')->db();
        $today = (new AttendanceModel($db))->forEmployeeAndDate($employeeId, date('Y-m-d'));

        $openPunch = $db->table('attendance_logs')
            ->where('employee_id', $employeeId)
            ->orderBy('punch_time', 'DESC')
            ->limit(1)->get()->getRowArray();

        $balance = $db->table('employee_leave_balances')
            ->selectSum('closing_balance')
            ->where('employee_id', $employeeId)
            ->where('financial_year', leave_financial_year())
            ->get()->getRowArray();

        $pendingRegularizations = $db->table('attendance_regularizations')
            ->where('employee_id', $employeeId)->where('status', 'pending')->countAllResults();

        return [
            'today_status'             => $today['status'] ?? null,
            'punched_in'               => $openPunch && $openPunch['punch_type'] === 'in',
            'leave_balance'            => (float) ($balance['closing_balance'] ?? 0),
            'pending_regularizations'  => $pendingRegularizations,
        ];
    }
}
