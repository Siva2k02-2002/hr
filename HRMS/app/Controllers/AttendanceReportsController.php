<?php

namespace App\Controllers;

use App\Models\AttendanceWeeklyOffModel;
use App\Services\AttendanceExportService;
use App\Services\AttendanceWeeklyOffService;
use CodeIgniter\Exceptions\PageNotFoundException;

/**
 * One query-builder per report type; AttendanceExportService formats
 * whatever (columns, rows) pair each report produces into xlsx/csv/pdf.
 */
class AttendanceReportsController extends BaseController
{
    private const TYPES = ['daily', 'monthly', 'employee', 'late', 'missing_punch', 'overtime', 'holiday', 'weekly_off'];

    public function index()
    {
        return view('attendance/reports/index', ['title' => 'Attendance Reports', 'types' => self::TYPES]);
    }

    public function view($type)
    {
        if (! in_array($type, self::TYPES, true)) {
            throw PageNotFoundException::forPageNotFound();
        }

        [$reportTitle, $columns, $rows] = $this->build($type);
        [$paged, $pager] = $this->paginateRows($rows);

        return view('attendance/reports/show', ['title' => $reportTitle, 'type' => $type, 'columns' => $columns, 'rows' => $paged, 'pager' => $pager, 'totalRows' => count($rows)]);
    }

    public function export($type, $format)
    {
        if (! in_array($type, self::TYPES, true) || ! in_array($format, ['xlsx', 'csv', 'pdf'], true)) {
            throw PageNotFoundException::forPageNotFound();
        }

        [$reportTitle, $columns, $rows] = $this->build($type);
        $service = new AttendanceExportService();

        [$content, $mime, $ext] = match ($format) {
            'xlsx' => [$service->toXlsx($columns, $rows), 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'xlsx'],
            'csv'  => [$service->toCsv($columns, $rows), 'text/csv', 'csv'],
            'pdf'  => [$service->toPdf($reportTitle, $columns, $rows), 'application/pdf', 'pdf'],
        };

        return $this->response
            ->setHeader('Content-Type', $mime)
            ->setHeader('Content-Disposition', 'attachment; filename="' . $type . '_report.' . $ext . '"')
            ->setBody($content);
    }

    /** @return array{0:string, 1:array<string,string>, 2:array<int,array<string,mixed>>} */
    private function build(string $type): array
    {
        $db = service('tenantContext')->db();

        return match ($type) {
            'daily' => $this->dailyReport($db),
            'monthly' => $this->monthlyReport($db),
            'employee' => $this->employeeReport($db),
            'late' => $this->lateReport($db),
            'missing_punch' => $this->missingPunchReport($db),
            'overtime' => $this->overtimeReport($db),
            'holiday' => $this->holidayReport($db),
            'weekly_off' => $this->weeklyOffReport($db),
        };
    }

    private function dailyReport($db): array
    {
        $date = (string) $this->request->getGet('date') ?: date('Y-m-d');
        $rows = $db->table('attendance a')
            ->select("e.employee_code, CONCAT(e.first_name,' ',e.last_name) as name, b.name as branch, d.name as department, a.status, a.first_punch_in_at, a.last_punch_out_at, a.working_minutes, a.late_minutes")
            ->join('employees e', 'e.id = a.employee_id')
            ->join('branches b', 'b.id = e.branch_id')
            ->join('departments d', 'd.id = e.department_id')
            ->where('a.attendance_date', $date)
            ->orderBy('e.first_name')
            ->get()->getResultArray();

        $columns = ['employee_code' => 'Employee Code', 'name' => 'Name', 'branch' => 'Branch', 'department' => 'Department', 'status' => 'Status', 'first_punch_in_at' => 'Punch In', 'last_punch_out_at' => 'Punch Out', 'working_minutes' => 'Working Min', 'late_minutes' => 'Late Min'];

        return ["Daily Attendance — {$date}", $columns, $this->localizePunchTimes($rows)];
    }

    private function monthlyReport($db): array
    {
        $year  = (int) ($this->request->getGet('year') ?: date('Y'));
        $month = (int) ($this->request->getGet('month') ?: date('n'));

        $rows = $db->query("
            SELECT e.employee_code, CONCAT(e.first_name,' ',e.last_name) as name,
                SUM(a.status='present') as present_days, SUM(a.status='absent') as absent_days,
                SUM(a.status='half_day') as half_days, SUM(a.status='late') as late_days,
                SUM(a.status='holiday') as holidays, SUM(a.status='weekly_off') as weekly_offs,
                ROUND(SUM(a.working_minutes)/60, 1) as total_hours, ROUND(SUM(a.overtime_minutes)/60, 1) as overtime_hours
            FROM employees e
            LEFT JOIN attendance a ON a.employee_id = e.id AND YEAR(a.attendance_date) = ? AND MONTH(a.attendance_date) = ?
            WHERE e.status != 'terminated' AND e.deleted_at IS NULL
            GROUP BY e.id
            ORDER BY e.first_name
        ", [$year, $month])->getResultArray();

        $columns = ['employee_code' => 'Employee Code', 'name' => 'Name', 'present_days' => 'Present', 'absent_days' => 'Absent', 'half_days' => 'Half Day', 'late_days' => 'Late', 'holidays' => 'Holiday', 'weekly_offs' => 'Weekly Off', 'total_hours' => 'Total Hours', 'overtime_hours' => 'OT Hours'];

        return ["Monthly Attendance — {$year}-{$month}", $columns, $rows];
    }

    private function employeeReport($db): array
    {
        $employeeId = (int) $this->request->getGet('employee_id');
        $dateFrom   = (string) $this->request->getGet('date_from') ?: date('Y-m-01');
        $dateTo     = (string) $this->request->getGet('date_to') ?: date('Y-m-d');

        $rows = $employeeId ? $db->table('attendance')
            ->where('employee_id', $employeeId)->where('attendance_date >=', $dateFrom)->where('attendance_date <=', $dateTo)
            ->orderBy('attendance_date')->get()->getResultArray() : [];

        $columns = ['attendance_date' => 'Date', 'status' => 'Status', 'first_punch_in_at' => 'Punch In', 'last_punch_out_at' => 'Punch Out', 'working_minutes' => 'Working Min', 'late_minutes' => 'Late Min', 'overtime_minutes' => 'OT Min'];

        return ['Employee Attendance', $columns, $this->localizePunchTimes($rows)];
    }

    private function lateReport($db): array
    {
        [$dateFrom, $dateTo] = $this->dateRange();
        $rows = $db->table('attendance a')
            ->select("e.employee_code, CONCAT(e.first_name,' ',e.last_name) as name, a.attendance_date, a.first_punch_in_at, a.late_minutes")
            ->join('employees e', 'e.id = a.employee_id')
            ->where('a.late_minutes >', 0)->where('a.attendance_date >=', $dateFrom)->where('a.attendance_date <=', $dateTo)
            ->orderBy('a.attendance_date', 'DESC')->get()->getResultArray();

        $columns = ['employee_code' => 'Employee Code', 'name' => 'Name', 'attendance_date' => 'Date', 'first_punch_in_at' => 'Punch In', 'late_minutes' => 'Late Min'];

        return ['Late Report', $columns, $this->localizePunchTimes($rows)];
    }

    private function missingPunchReport($db): array
    {
        [$dateFrom, $dateTo] = $this->dateRange();
        $rows = $db->table('attendance a')
            ->select("e.employee_code, CONCAT(e.first_name,' ',e.last_name) as name, a.attendance_date, a.first_punch_in_at, a.last_punch_out_at")
            ->join('employees e', 'e.id = a.employee_id')
            ->where('a.status', 'missed_punch')->where('a.attendance_date >=', $dateFrom)->where('a.attendance_date <=', $dateTo)
            ->orderBy('a.attendance_date', 'DESC')->get()->getResultArray();

        $columns = ['employee_code' => 'Employee Code', 'name' => 'Name', 'attendance_date' => 'Date', 'first_punch_in_at' => 'Punch In', 'last_punch_out_at' => 'Punch Out'];

        return ['Missing Punch Report', $columns, $this->localizePunchTimes($rows)];
    }

    private function overtimeReport($db): array
    {
        [$dateFrom, $dateTo] = $this->dateRange();
        $rows = $db->table('attendance_overtime o')
            ->select("e.employee_code, CONCAT(e.first_name,' ',e.last_name) as name, o.attendance_date, o.shift_minutes, o.worked_minutes, o.overtime_minutes, o.status")
            ->join('employees e', 'e.id = o.employee_id')
            ->where('o.attendance_date >=', $dateFrom)->where('o.attendance_date <=', $dateTo)
            ->orderBy('o.attendance_date', 'DESC')->get()->getResultArray();

        $columns = ['employee_code' => 'Employee Code', 'name' => 'Name', 'attendance_date' => 'Date', 'shift_minutes' => 'Shift Min', 'worked_minutes' => 'Worked Min', 'overtime_minutes' => 'OT Min', 'status' => 'Status'];

        return ['Overtime Report', $columns, $rows];
    }

    private function holidayReport($db): array
    {
        $year = (int) ($this->request->getGet('year') ?: date('Y'));
        $rows = $db->table('attendance_holidays h')
            ->select('h.name, h.date, h.holiday_type, b.name as branch, h.is_optional')
            ->join('branches b', 'b.id = h.branch_id', 'left')
            ->where('YEAR(h.date)', $year)->where('h.status', 'active')
            ->orderBy('h.date')->get()->getResultArray();

        $columns = ['name' => 'Holiday', 'date' => 'Date', 'holiday_type' => 'Type', 'branch' => 'Branch', 'is_optional' => 'Optional'];

        return ["Holiday Report — {$year}", $columns, $rows];
    }

    private function weeklyOffReport($db): array
    {
        $rows = (new AttendanceWeeklyOffModel($db))
            ->select('attendance_weekly_offs.name, day_of_week, week_pattern, b.name as branch, s.name as shift')
            ->join('branches b', 'b.id = attendance_weekly_offs.branch_id', 'left')
            ->join('attendance_shifts s', 's.id = attendance_weekly_offs.shift_id', 'left')
            ->where('attendance_weekly_offs.status', 'active')
            ->findAll();

        $columns = ['name' => 'Rule', 'day_of_week' => 'Day', 'week_pattern' => 'Pattern', 'branch' => 'Branch', 'shift' => 'Shift'];

        return ['Weekly Off Report', $columns, $rows];
    }

    /** first_punch_in_at/last_punch_out_at are stored in the app's storage timezone (UTC) — convert for display/export. */
    private function localizePunchTimes(array $rows): array
    {
        foreach ($rows as &$row) {
            foreach (['first_punch_in_at', 'last_punch_out_at'] as $key) {
                if (! empty($row[$key])) {
                    $row[$key] = local_time($row[$key]);
                }
            }
        }

        return $rows;
    }

    private function dateRange(): array
    {
        return [
            (string) $this->request->getGet('date_from') ?: date('Y-m-01'),
            (string) $this->request->getGet('date_to') ?: date('Y-m-d'),
        ];
    }
}
