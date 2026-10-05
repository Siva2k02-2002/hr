<?php

namespace App\Controllers;

use App\Models\EmployeeModel;
use App\Models\PayrollAdvanceModel;
use App\Models\PayrollEmployeeSalaryModel;
use App\Models\PayrollLoanModel;
use App\Models\PayrollReimbursementModel;
use App\Models\PayrollRunItemModel;
use App\Services\PayrollLoanService;
use App\Services\PayslipService;
use App\Services\SalaryStructureService;
use CodeIgniter\Exceptions\PageNotFoundException;

/** Employee Self-Service — every query here is scoped to the logged-in user's own employee record; there is no path from this controller to another employee's data. */
class MyPayrollController extends BaseController
{
    public function index()
    {
        $employee = $this->currentEmployee();
        if (! $employee) {
            return view('my_payroll/index', ['title' => 'My Payroll', 'employee' => null]);
        }

        $db          = service('tenantContext')->db();
        $payslips    = (new PayrollRunItemModel($db))->forEmployee((int) $employee['id']);
        $latestSlip  = $payslips[0] ?? null;
        $activeLoans = (new PayrollLoanModel($db))->where('employee_id', $employee['id'])->where('status', 'active')->countAllResults();
        $activeAdv   = (new PayrollAdvanceModel($db))->where('employee_id', $employee['id'])->where('status', 'active')->countAllResults();
        $pendingReim = (new PayrollReimbursementModel($db))->where('employee_id', $employee['id'])->where('status', 'pending')->countAllResults();

        return view('my_payroll/index', [
            'title'    => 'My Payroll',
            'employee' => $employee,
            'latest'   => $latestSlip,
            'cards'    => ['active_loans' => $activeLoans, 'active_advances' => $activeAdv, 'pending_reimbursements' => $pendingReim],
            'recent'   => array_slice($payslips, 0, 6),
        ]);
    }

    public function payslips()
    {
        $employee = $this->currentEmployee();
        if (! $employee) {
            return redirect()->to(site_url('my-payroll'));
        }

        return view('my_payroll/payslips', [
            'title'    => 'My Payslips',
            'employee' => $employee,
            'items'    => (new PayrollRunItemModel(service('tenantContext')->db()))->forEmployee((int) $employee['id']),
        ]);
    }

    public function download($runItemId)
    {
        $employee = $this->currentEmployee();
        $item     = (new PayrollRunItemModel(service('tenantContext')->db()))->find($runItemId);
        if (! $employee || ! $item || (int) $item['employee_id'] !== (int) $employee['id']) {
            throw PageNotFoundException::forPageNotFound();
        }

        $service = new PayslipService();
        $payslip = $service->ensurePayslip((int) $runItemId);
        $pdf     = $service->renderPdf((int) $runItemId);
        $service->recordDownload((int) $payslip['id']);

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $payslip['payslip_number'] . '.pdf"')
            ->setBody($pdf);
    }

    public function salarySummary()
    {
        $employee = $this->currentEmployee();
        if (! $employee) {
            return redirect()->to(site_url('my-payroll'));
        }

        $assignment = (new PayrollEmployeeSalaryModel(service('tenantContext')->db()))->activeFor((int) $employee['id']);
        $items      = [];
        $calc       = null;
        if ($assignment) {
            $service = new SalaryStructureService();
            $items   = $service->items((int) $assignment['salary_structure_id']);
            $calc    = $service->calculate($items, (float) $assignment['gross_salary']);
        }

        return view('my_payroll/salary_summary', ['title' => 'Salary Summary', 'employee' => $employee, 'assignment' => $assignment, 'calc' => $calc]);
    }

    public function loans()
    {
        $employee = $this->currentEmployee();
        if (! $employee) {
            return redirect()->to(site_url('my-payroll'));
        }

        $loanService = new PayrollLoanService();
        $loans       = $loanService->forEmployee((int) $employee['id']);

        return view('my_payroll/loans', ['title' => 'My Loans', 'employee' => $employee, 'loans' => $loans]);
    }

    public function advances()
    {
        $employee = $this->currentEmployee();
        if (! $employee) {
            return redirect()->to(site_url('my-payroll'));
        }

        return view('my_payroll/advances', [
            'title'    => 'My Advances',
            'employee' => $employee,
            'advances' => (new PayrollAdvanceModel(service('tenantContext')->db()))->forEmployee((int) $employee['id']),
        ]);
    }

    public function reimbursements()
    {
        $employee = $this->currentEmployee();
        if (! $employee) {
            return redirect()->to(site_url('my-payroll'));
        }

        return view('my_payroll/reimbursements', [
            'title'          => 'My Reimbursements',
            'employee'       => $employee,
            'reimbursements' => (new PayrollReimbursementModel(service('tenantContext')->db()))->forEmployee((int) $employee['id']),
        ]);
    }

    public function taxSummary()
    {
        return view('my_payroll/tax_summary', ['title' => 'Tax Summary']);
    }

    private function currentEmployee(): ?array
    {
        return (new EmployeeModel(service('tenantContext')->db()))->where('user_id', session('tenant_user_id'))->first();
    }
}
