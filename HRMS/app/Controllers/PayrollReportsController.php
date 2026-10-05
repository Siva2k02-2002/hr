<?php

namespace App\Controllers;

use App\Models\PayrollMonthModel;
use App\Models\PayrollRunModel;
use App\Services\PayrollBankTransferService;
use App\Services\PayrollExportService;
use CodeIgniter\Exceptions\PageNotFoundException;

/** One query-builder per report type; PayrollExportService formats whatever (columns, rows) pair each report produces — same shape as LeaveReportsController/AttendanceReportsController. */
class PayrollReportsController extends BaseController
{
    private const TYPES = [
        'register', 'salary_register', 'bank_transfer', 'pf', 'esi', 'pt', 'tds', 'lop', 'overtime',
        'loan', 'bonus', 'incentive', 'reimbursement', 'payslip',
    ];

    public function index()
    {
        return view('payroll/reports/index', ['title' => 'Payroll Reports', 'types' => self::TYPES]);
    }

    public function view($type)
    {
        if (! in_array($type, self::TYPES, true)) {
            throw PageNotFoundException::forPageNotFound();
        }

        [$reportTitle, $columns, $rows] = $this->build($type);
        [$paged, $pager] = $this->paginateRows($rows);

        return view('payroll/reports/show', ['title' => $reportTitle, 'type' => $type, 'columns' => $columns, 'rows' => $paged, 'pager' => $pager, 'totalRows' => count($rows)]);
    }

    public function export($type, $format)
    {
        if (! in_array($type, self::TYPES, true) || ! in_array($format, ['xlsx', 'csv', 'pdf'], true)) {
            throw PageNotFoundException::forPageNotFound();
        }

        [$reportTitle, $columns, $rows] = $this->build($type);
        $service = new PayrollExportService();

        [$content, $mime, $ext] = match ($format) {
            'xlsx' => [$service->toXlsx($columns, $rows), 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'xlsx'],
            'csv'  => [$service->toCsv($columns, $rows), 'text/csv', 'csv'],
            'pdf'  => [$service->toPdf($reportTitle, $columns, $rows), 'application/pdf', 'pdf'],
        };

        return $this->response
            ->setHeader('Content-Type', $mime)
            ->setHeader('Content-Disposition', 'attachment; filename="payroll_' . $type . '_report.' . $ext . '"')
            ->setBody($content);
    }

    private function build(string $type): array
    {
        $db = service('tenantContext')->db();

        return match ($type) {
            'register'      => $this->registerReport($db),
            'salary_register' => $this->salaryRegisterReport($db),
            'bank_transfer' => $this->bankTransferReport($db),
            'pf'            => $this->pfReport($db),
            'esi'           => $this->esiReport($db),
            'pt'            => $this->ptReport($db),
            'tds'           => $this->tdsReport($db),
            'lop'           => $this->lopReport($db),
            'overtime'      => $this->overtimeReport($db),
            'loan'          => $this->loanReport($db),
            'bonus'         => $this->bonusReport($db),
            'incentive'     => $this->incentiveReport($db),
            'reimbursement' => $this->reimbursementReport($db),
            'payslip'       => $this->payslipReport($db),
        };
    }

    private function period(): array
    {
        return [
            (int) ($this->request->getGet('year') ?: date('Y')),
            (int) ($this->request->getGet('month') ?: date('n')),
        ];
    }

    private function runItemsForPeriod($db): array
    {
        [$year, $month] = $this->period();

        return $db->table('payroll_run_items pri')
            ->select("pri.*, e.employee_code, CONCAT(e.first_name,' ',e.last_name) as employee_name, e.department_id, d.name as department_name")
            ->join('employees e', 'e.id = pri.employee_id')
            ->join('departments d', 'd.id = e.department_id', 'left')
            ->join('payroll_months m', 'm.id = pri.payroll_month_id')
            ->where('m.year', $year)->where('m.month', $month)
            ->whereIn('pri.status', ['approved', 'locked', 'paid'])
            ->orderBy('e.first_name')
            ->get()->getResultArray();
    }

    private function registerReport($db): array
    {
        [$year, $month] = $this->period();
        $rows    = $this->runItemsForPeriod($db);
        $columns = [
            'employee_code' => 'Employee Code', 'employee_name' => 'Name', 'department_name' => 'Department',
            'working_days' => 'Working Days', 'lop_days' => 'LOP Days', 'gross_earnings' => 'Gross Earnings',
            'gross_deductions' => 'Gross Deductions', 'net_salary' => 'Net Salary',
        ];

        return ['Payroll Register — ' . payroll_period_label($month, $year), $columns, $rows];
    }

    private function salaryRegisterReport($db): array
    {
        [$year, $month] = $this->period();
        $rows = $db->table('payroll_run_items pri')
            ->select("e.employee_code, CONCAT(e.first_name,' ',e.last_name) as employee_name, s.name as structure_name, pri.gross_earnings, pri.pf_employee, pri.esi_employee, pri.professional_tax, pri.tds, pri.net_salary")
            ->join('employees e', 'e.id = pri.employee_id')
            ->join('payroll_salary_structures s', 's.id = pri.salary_structure_id', 'left')
            ->join('payroll_months m', 'm.id = pri.payroll_month_id')
            ->where('m.year', $year)->where('m.month', $month)
            ->whereIn('pri.status', ['approved', 'locked', 'paid'])
            ->orderBy('e.first_name')->get()->getResultArray();

        $columns = [
            'employee_code' => 'Employee Code', 'employee_name' => 'Name', 'structure_name' => 'Structure',
            'gross_earnings' => 'Gross', 'pf_employee' => 'PF', 'esi_employee' => 'ESI',
            'professional_tax' => 'PT', 'tds' => 'TDS', 'net_salary' => 'Net Salary',
        ];

        return ['Salary Register — ' . payroll_period_label($month, $year), $columns, $rows];
    }

    /** Bound to whichever non-cancelled run exists for the selected month — a period has at most one, per PayrollRunModel::activeForMonth(). */
    private function bankTransferReport($db): array
    {
        [$year, $month] = $this->period();
        $payrollMonth = (new PayrollMonthModel($db))->forMonthYear($month, $year);
        $run          = $payrollMonth ? (new PayrollRunModel($db))->activeForMonth((int) $payrollMonth['id']) : null;
        $rows         = $run ? (new PayrollBankTransferService())->rowsForRun((int) $run['id']) : [];

        $columns = ['employee_code' => 'Employee Code', 'employee_name' => 'Employee Name', 'bank_name' => 'Bank Name', 'account_number' => 'Account Number', 'ifsc_code' => 'IFSC Code', 'net_salary' => 'Net Salary'];

        return ['Bank Transfer Report — ' . payroll_period_label($month, $year), $columns, $rows];
    }

    private function pfReport($db): array
    {
        [$year, $month] = $this->period();
        $rows    = $this->runItemsForPeriod($db);
        $columns = ['employee_code' => 'Employee Code', 'employee_name' => 'Name', 'pf_employee' => 'Employee PF', 'pf_employer' => 'Employer PF'];

        return ['PF Report — ' . payroll_period_label($month, $year), $columns, $rows];
    }

    private function esiReport($db): array
    {
        [$year, $month] = $this->period();
        $rows    = $this->runItemsForPeriod($db);
        $columns = ['employee_code' => 'Employee Code', 'employee_name' => 'Name', 'esi_employee' => 'Employee ESI', 'esi_employer' => 'Employer ESI'];

        return ['ESI Report — ' . payroll_period_label($month, $year), $columns, $rows];
    }

    private function ptReport($db): array
    {
        [$year, $month] = $this->period();
        $rows    = $this->runItemsForPeriod($db);
        $columns = ['employee_code' => 'Employee Code', 'employee_name' => 'Name', 'professional_tax' => 'Professional Tax'];

        return ['Professional Tax Report — ' . payroll_period_label($month, $year), $columns, $rows];
    }

    private function tdsReport($db): array
    {
        [$year, $month] = $this->period();
        $rows    = $this->runItemsForPeriod($db);
        $columns = ['employee_code' => 'Employee Code', 'employee_name' => 'Name', 'gross_earnings' => 'Gross Earnings', 'tds' => 'TDS'];

        return ['TDS Report — ' . payroll_period_label($month, $year), $columns, $rows];
    }

    private function lopReport($db): array
    {
        [$year, $month] = $this->period();
        $rows = array_map(static function ($row) {
            $deductions       = json_decode((string) $row['deductions_breakdown'], true) ?: [];
            $row['lop_amount'] = $deductions['lop_amount'] ?? 0;

            return $row;
        }, $this->runItemsForPeriod($db));

        $columns = ['employee_code' => 'Employee Code', 'employee_name' => 'Name', 'working_days' => 'Working Days', 'lop_days' => 'LOP Days', 'lop_amount' => 'LOP Amount'];

        return ['LOP Report — ' . payroll_period_label($month, $year), $columns, $rows];
    }

    private function overtimeReport($db): array
    {
        [$year, $month] = $this->period();
        $rows    = $this->runItemsForPeriod($db);
        $columns = ['employee_code' => 'Employee Code', 'employee_name' => 'Name', 'overtime_hours' => 'OT Hours', 'overtime_amount' => 'OT Amount'];

        return ['Overtime Report — ' . payroll_period_label($month, $year), $columns, $rows];
    }

    private function loanReport($db): array
    {
        [$year, $month] = $this->period();
        $rows = $db->table('payroll_loan_installments li')
            ->select("e.employee_code, CONCAT(e.first_name,' ',e.last_name) as employee_name, l.loan_number, l.loan_type, li.installment_no, li.emi_amount, li.status")
            ->join('payroll_loans l', 'l.id = li.loan_id')
            ->join('employees e', 'e.id = l.employee_id')
            ->where('li.due_year', $year)->where('li.due_month', $month)
            ->orderBy('e.first_name')->get()->getResultArray();

        $columns = ['employee_code' => 'Employee Code', 'employee_name' => 'Name', 'loan_number' => 'Loan #', 'loan_type' => 'Type', 'installment_no' => 'Installment', 'emi_amount' => 'EMI', 'status' => 'Status'];

        return ['Loan Report — ' . payroll_period_label($month, $year), $columns, $rows];
    }

    private function bonusReport($db): array
    {
        [$year, $month] = $this->period();
        $rows = $db->table('payroll_bonus pb')
            ->select("e.employee_code, CONCAT(e.first_name,' ',e.last_name) as employee_name, pb.bonus_type, pb.amount, pb.status")
            ->join('employees e', 'e.id = pb.employee_id')
            ->join('payroll_months m', 'm.id = pb.payroll_month_id', 'left')
            ->groupStart()->where('m.year', $year)->where('m.month', $month)->groupEnd()
            ->orderBy('e.first_name')->get()->getResultArray();

        $columns = ['employee_code' => 'Employee Code', 'employee_name' => 'Name', 'bonus_type' => 'Type', 'amount' => 'Amount', 'status' => 'Status'];

        return ['Bonus Report — ' . payroll_period_label($month, $year), $columns, $rows];
    }

    private function incentiveReport($db): array
    {
        [$year, $month] = $this->period();
        $rows = $db->table('payroll_incentives pi')
            ->select("e.employee_code, CONCAT(e.first_name,' ',e.last_name) as employee_name, pi.incentive_type, pi.amount, pi.status")
            ->join('employees e', 'e.id = pi.employee_id')
            ->join('payroll_months m', 'm.id = pi.payroll_month_id', 'left')
            ->groupStart()->where('m.year', $year)->where('m.month', $month)->groupEnd()
            ->orderBy('e.first_name')->get()->getResultArray();

        $columns = ['employee_code' => 'Employee Code', 'employee_name' => 'Name', 'incentive_type' => 'Type', 'amount' => 'Amount', 'status' => 'Status'];

        return ['Incentive Report — ' . payroll_period_label($month, $year), $columns, $rows];
    }

    private function reimbursementReport($db): array
    {
        [$year, $month] = $this->period();
        $rows = $db->table('payroll_reimbursements pr')
            ->select("e.employee_code, CONCAT(e.first_name,' ',e.last_name) as employee_name, pr.expense_type, pr.amount, pr.status")
            ->join('employees e', 'e.id = pr.employee_id')
            ->join('payroll_months m', 'm.id = pr.payroll_month_id', 'left')
            ->groupStart()->where('m.year', $year)->where('m.month', $month)->groupEnd()
            ->orderBy('e.first_name')->get()->getResultArray();

        $columns = ['employee_code' => 'Employee Code', 'employee_name' => 'Name', 'expense_type' => 'Expense Type', 'amount' => 'Amount', 'status' => 'Status'];

        return ['Reimbursement Report — ' . payroll_period_label($month, $year), $columns, $rows];
    }

    private function payslipReport($db): array
    {
        [$year, $month] = $this->period();
        $rows = $db->table('payroll_payslips ps')
            ->select("e.employee_code, CONCAT(e.first_name,' ',e.last_name) as employee_name, ps.payslip_number, ps.generated_at, ps.downloaded_count")
            ->join('employees e', 'e.id = ps.employee_id')
            ->join('payroll_months m', 'm.id = ps.payroll_month_id')
            ->where('m.year', $year)->where('m.month', $month)
            ->orderBy('e.first_name')->get()->getResultArray();

        $columns = ['employee_code' => 'Employee Code', 'employee_name' => 'Name', 'payslip_number' => 'Payslip #', 'generated_at' => 'Generated At', 'downloaded_count' => 'Downloads'];

        return ['Payslip Report — ' . payroll_period_label($month, $year), $columns, $rows];
    }
}
