<?php

namespace App\Controllers;

use App\Services\LeaveExportService;
use CodeIgniter\Exceptions\PageNotFoundException;

/** One query-builder per report type; LeaveExportService formats whatever (columns, rows) pair each report produces — same shape as AttendanceReportsController. */
class LeaveReportsController extends BaseController
{
    private const TYPES = ['summary', 'balance', 'register', 'pending', 'department', 'type', 'monthly', 'carry_forward'];

    public function index()
    {
        return view('leave/reports/index', ['title' => 'Leave Reports', 'types' => self::TYPES]);
    }

    public function view($type)
    {
        if (! in_array($type, self::TYPES, true)) {
            throw PageNotFoundException::forPageNotFound();
        }

        [$reportTitle, $columns, $rows] = $this->build($type);
        [$paged, $pager] = $this->paginateRows($rows);

        return view('leave/reports/show', ['title' => $reportTitle, 'type' => $type, 'columns' => $columns, 'rows' => $paged, 'pager' => $pager, 'totalRows' => count($rows)]);
    }

    public function export($type, $format)
    {
        if (! in_array($type, self::TYPES, true) || ! in_array($format, ['xlsx', 'csv', 'pdf'], true)) {
            throw PageNotFoundException::forPageNotFound();
        }

        [$reportTitle, $columns, $rows] = $this->build($type);
        $service = new LeaveExportService();

        [$content, $mime, $ext] = match ($format) {
            'xlsx' => [$service->toXlsx($columns, $rows), 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'xlsx'],
            'csv'  => [$service->toCsv($columns, $rows), 'text/csv', 'csv'],
            'pdf'  => [$service->toPdf($reportTitle, $columns, $rows), 'application/pdf', 'pdf'],
        };

        return $this->response
            ->setHeader('Content-Type', $mime)
            ->setHeader('Content-Disposition', 'attachment; filename="leave_' . $type . '_report.' . $ext . '"')
            ->setBody($content);
    }

    /** @return array{0:string, 1:array<string,string>, 2:array<int,array<string,mixed>>} */
    private function build(string $type): array
    {
        $db = service('tenantContext')->db();

        return match ($type) {
            'summary'       => $this->summaryReport($db),
            'balance'       => $this->balanceReport($db),
            'register'      => $this->registerReport($db),
            'pending'       => $this->pendingReport($db),
            'department'    => $this->departmentReport($db),
            'type'          => $this->typeReport($db),
            'monthly'       => $this->monthlyReport($db),
            'carry_forward' => $this->carryForwardReport($db),
        };
    }

    private function summaryReport($db): array
    {
        [$dateFrom, $dateTo] = $this->dateRange();
        $rows = $db->query('
            SELECT lt.name as leave_type, lt.code,
                COUNT(*) as applications,
                SUM(la.status = "approved") as approved_count,
                SUM(la.status = "pending") as pending_count,
                SUM(la.status = "rejected") as rejected_count,
                SUM(CASE WHEN la.status = "approved" THEN la.total_days ELSE 0 END) as approved_days
            FROM leave_applications la
            JOIN leave_types lt ON lt.id = la.leave_type_id
            WHERE la.from_date >= ? AND la.from_date <= ?
            GROUP BY lt.id
            ORDER BY lt.sort_order
        ', [$dateFrom, $dateTo])->getResultArray();

        $columns = ['leave_type' => 'Leave Type', 'code' => 'Code', 'applications' => 'Applications', 'approved_count' => 'Approved', 'pending_count' => 'Pending', 'rejected_count' => 'Rejected', 'approved_days' => 'Approved Days'];

        return ["Leave Summary — {$dateFrom} to {$dateTo}", $columns, $rows];
    }

    private function balanceReport($db): array
    {
        $fy = (int) ($this->request->getGet('fy') ?: leave_financial_year());
        $rows = $db->query('
            SELECT e.employee_code, CONCAT(e.first_name, " ", e.last_name) as name, lt.name as leave_type,
                b.opening_balance, b.earned, b.availed, b.adjusted, b.carry_forward_in, b.encashed, b.closing_balance
            FROM employee_leave_balances b
            JOIN employees e ON e.id = b.employee_id
            JOIN leave_types lt ON lt.id = b.leave_type_id
            WHERE b.financial_year = ? AND e.deleted_at IS NULL
            ORDER BY e.first_name, lt.sort_order
        ', [$fy])->getResultArray();

        $columns = ['employee_code' => 'Employee Code', 'name' => 'Name', 'leave_type' => 'Leave Type', 'opening_balance' => 'Opening', 'earned' => 'Earned', 'availed' => 'Availed', 'adjusted' => 'Adjusted', 'carry_forward_in' => 'Carry Fwd In', 'encashed' => 'Encashed', 'closing_balance' => 'Closing'];

        return ["Employee Leave Balance — FY {$fy}", $columns, $rows];
    }

    private function registerReport($db): array
    {
        [$dateFrom, $dateTo] = $this->dateRange();
        $rows = $db->table('leave_applications la')
            ->select('e.employee_code, CONCAT(e.first_name," ",e.last_name) as name, lt.name as leave_type, la.from_date, la.to_date, la.total_days, la.status, la.submitted_at')
            ->join('employees e', 'e.id = la.employee_id')
            ->join('leave_types lt', 'lt.id = la.leave_type_id')
            ->where('la.from_date >=', $dateFrom)->where('la.from_date <=', $dateTo)
            ->orderBy('la.from_date', 'DESC')->get()->getResultArray();

        $columns = ['employee_code' => 'Employee Code', 'name' => 'Name', 'leave_type' => 'Leave Type', 'from_date' => 'From', 'to_date' => 'To', 'total_days' => 'Days', 'status' => 'Status', 'submitted_at' => 'Applied On'];

        return ["Leave Register — {$dateFrom} to {$dateTo}", $columns, $rows];
    }

    private function pendingReport($db): array
    {
        $rows = $db->table('leave_applications la')
            ->select('e.employee_code, CONCAT(e.first_name," ",e.last_name) as name, lt.name as leave_type, la.from_date, la.to_date, la.total_days, la.current_level, la.submitted_at')
            ->join('employees e', 'e.id = la.employee_id')
            ->join('leave_types lt', 'lt.id = la.leave_type_id')
            ->where('la.status', 'pending')
            ->orderBy('la.submitted_at')->get()->getResultArray();

        $columns = ['employee_code' => 'Employee Code', 'name' => 'Name', 'leave_type' => 'Leave Type', 'from_date' => 'From', 'to_date' => 'To', 'total_days' => 'Days', 'current_level' => 'Pending At', 'submitted_at' => 'Applied On'];

        return ['Pending Approvals', $columns, $rows];
    }

    private function departmentReport($db): array
    {
        [$dateFrom, $dateTo] = $this->dateRange();
        $rows = $db->query('
            SELECT d.name as department, COUNT(*) as applications,
                SUM(CASE WHEN la.status = "approved" THEN la.total_days ELSE 0 END) as approved_days
            FROM leave_applications la
            JOIN employees e ON e.id = la.employee_id
            JOIN departments d ON d.id = e.department_id
            WHERE la.from_date >= ? AND la.from_date <= ?
            GROUP BY d.id
            ORDER BY d.name
        ', [$dateFrom, $dateTo])->getResultArray();

        $columns = ['department' => 'Department', 'applications' => 'Applications', 'approved_days' => 'Approved Days'];

        return ["Department Leave Report — {$dateFrom} to {$dateTo}", $columns, $rows];
    }

    private function typeReport($db): array
    {
        [$dateFrom, $dateTo] = $this->dateRange();
        $rows = $db->query('
            SELECT lt.name as leave_type, lt.is_paid, COUNT(*) as applications,
                SUM(CASE WHEN la.status = "approved" THEN la.total_days ELSE 0 END) as approved_days,
                ROUND(AVG(la.total_days), 1) as avg_days
            FROM leave_applications la
            JOIN leave_types lt ON lt.id = la.leave_type_id
            WHERE la.from_date >= ? AND la.from_date <= ?
            GROUP BY lt.id
            ORDER BY lt.sort_order
        ', [$dateFrom, $dateTo])->getResultArray();

        $columns = ['leave_type' => 'Leave Type', 'is_paid' => 'Paid', 'applications' => 'Applications', 'approved_days' => 'Approved Days', 'avg_days' => 'Avg Days/App'];

        return ["Leave Type Report — {$dateFrom} to {$dateTo}", $columns, $rows];
    }

    private function monthlyReport($db): array
    {
        $year  = (int) ($this->request->getGet('year') ?: date('Y'));
        $month = (int) ($this->request->getGet('month') ?: date('n'));

        $rows = $db->query('
            SELECT e.employee_code, CONCAT(e.first_name," ",e.last_name) as name,
                SUM(a.status = "leave") as full_leave_days, SUM(a.status = "half_day_leave") as half_leave_days,
                SUM(a.status = "lop") as lop_days, SUM(a.status = "work_from_home") as wfh_days, SUM(a.status = "on_duty") as on_duty_days
            FROM employees e
            LEFT JOIN attendance a ON a.employee_id = e.id AND YEAR(a.attendance_date) = ? AND MONTH(a.attendance_date) = ?
            WHERE e.status != "terminated" AND e.deleted_at IS NULL
            GROUP BY e.id
            ORDER BY e.first_name
        ', [$year, $month])->getResultArray();

        $columns = ['employee_code' => 'Employee Code', 'name' => 'Name', 'full_leave_days' => 'Full Leave', 'half_leave_days' => 'Half Leave', 'lop_days' => 'LOP', 'wfh_days' => 'WFH', 'on_duty_days' => 'On Duty'];

        return ["Monthly Leave Report — {$year}-{$month}", $columns, $rows];
    }

    private function carryForwardReport($db): array
    {
        $rows = $db->table('leave_carry_forward_history h')
            ->select('e.employee_code, CONCAT(e.first_name," ",e.last_name) as name, lt.name as leave_type, h.from_financial_year, h.to_financial_year, h.eligible_balance, h.carried_forward_days, h.expired_days, h.run_batch_id')
            ->join('employees e', 'e.id = h.employee_id')
            ->join('leave_types lt', 'lt.id = h.leave_type_id')
            ->orderBy('h.created_at', 'DESC')->get()->getResultArray();

        $columns = ['employee_code' => 'Employee Code', 'name' => 'Name', 'leave_type' => 'Leave Type', 'from_financial_year' => 'From FY', 'to_financial_year' => 'To FY', 'eligible_balance' => 'Eligible', 'carried_forward_days' => 'Carried', 'expired_days' => 'Expired', 'run_batch_id' => 'Batch'];

        return ['Carry Forward Report', $columns, $rows];
    }

    private function dateRange(): array
    {
        return [
            (string) $this->request->getGet('date_from') ?: date('Y-m-01'),
            (string) $this->request->getGet('date_to') ?: date('Y-m-d'),
        ];
    }
}
