<?php

namespace App\Controllers;

use App\Models\AttendanceHolidayModel;
use App\Models\DepartmentModel;
use App\Models\LeaveApplicationDayModel;

/**
 * Month view is the full day-grid; "year" is a per-month summary count
 * (a full 12x31 grid would be unreadable at this app's compact-table
 * density); "team"/"department" are filters applied to the same month
 * grid via manager_id/department_id, not separate layouts — this keeps
 * one calendar engine instead of three parallel ones.
 */
class LeaveCalendarController extends BaseController
{
    public function index()
    {
        $db    = service('tenantContext')->db();
        $year  = (int) ($this->request->getGet('year') ?: date('Y'));
        $month = (int) ($this->request->getGet('month') ?: date('n'));
        $view  = in_array((string) $this->request->getGet('view'), ['month', 'year'], true) ? $this->request->getGet('view') : 'month';

        $filters = [
            'employee_id'   => (string) $this->request->getGet('employee_id'),
            'department_id' => (string) $this->request->getGet('department_id'),
            'manager_id'    => (string) $this->request->getGet('manager_id'),
        ];

        if ($view === 'year') {
            $monthCounts = [];
            for ($m = 1; $m <= 12; $m++) {
                $monthCounts[$m] = $this->countForMonth($db, $year, $m, $filters);
            }

            return view('leave/calendar/index', [
                'title' => 'Leave Calendar', 'view' => 'year', 'year' => $year, 'month' => $month,
                'monthCounts' => $monthCounts, 'filters' => $filters,
                'departments' => (new DepartmentModel($db))->where('status', 'active')->findAll(),
            ]);
        }

        $monthStart = sprintf('%04d-%02d-01', $year, $month);
        $monthEnd   = date('Y-m-t', strtotime($monthStart));

        $model = (new LeaveApplicationDayModel($db))
            ->select("leave_application_days.leave_date, la.status, CONCAT(e.first_name, ' ', e.last_name) as employee_name, e.employee_code, e.department_id, e.reporting_manager_id, lt.name as leave_type_name, lt.color as leave_type_color")
            ->join('leave_applications la', 'la.id = leave_application_days.leave_application_id')
            ->join('employees e', 'e.id = la.employee_id')
            ->join('leave_types lt', 'lt.id = la.leave_type_id')
            ->where('leave_application_days.counts_as_leave', 1)
            ->whereIn('la.status', ['pending', 'approved'])
            ->where('leave_application_days.leave_date >=', $monthStart)
            ->where('leave_application_days.leave_date <=', $monthEnd);

        $this->applyEntryFilters($model, $filters);
        $entries = $model->findAll();

        $byDate = [];
        foreach ($entries as $entry) {
            $byDate[(int) date('j', strtotime($entry['leave_date']))][] = $entry;
        }

        $holidaysByDate = [];
        foreach ((new AttendanceHolidayModel($db))->forMonth($year, $month) as $holiday) {
            $holidaysByDate[(int) date('j', strtotime($holiday['date']))] = $holiday;
        }

        return view('leave/calendar/index', [
            'title' => 'Leave Calendar', 'view' => 'month', 'year' => $year, 'month' => $month,
            'byDate' => $byDate, 'holidaysByDate' => $holidaysByDate, 'filters' => $filters,
            'departments' => (new DepartmentModel($db))->where('status', 'active')->findAll(),
        ]);
    }

    private function countForMonth($db, int $year, int $month, array $filters): int
    {
        $monthStart = sprintf('%04d-%02d-01', $year, $month);
        $monthEnd   = date('Y-m-t', strtotime($monthStart));

        $model = (new LeaveApplicationDayModel($db))
            ->join('leave_applications la', 'la.id = leave_application_days.leave_application_id')
            ->join('employees e', 'e.id = la.employee_id')
            ->where('leave_application_days.counts_as_leave', 1)
            ->whereIn('la.status', ['pending', 'approved'])
            ->where('leave_application_days.leave_date >=', $monthStart)
            ->where('leave_application_days.leave_date <=', $monthEnd);

        $this->applyEntryFilters($model, $filters);

        return $model->countAllResults();
    }

    private function applyEntryFilters($model, array $filters): void
    {
        if ($filters['employee_id'] !== '') {
            $model->where('la.employee_id', $filters['employee_id']);
        }
        if ($filters['department_id'] !== '') {
            $model->where('e.department_id', $filters['department_id']);
        }
        if ($filters['manager_id'] !== '') {
            $model->where('e.reporting_manager_id', $filters['manager_id']);
        }
    }
}
