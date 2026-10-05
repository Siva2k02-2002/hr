<?php

namespace App\Controllers;

use App\Models\PayrollLoanModel;
use App\Services\PayrollLoanService;
use RuntimeException;

class PayrollLoansController extends BaseController
{
    public function index()
    {
        $status = (string) $this->request->getGet('status');
        $model  = (new PayrollLoanModel(service('tenantContext')->db()))->withEmployee();
        if ($status !== '') {
            $model->where('payroll_loans.status', $status);
        }

        $loans = $model->orderBy('payroll_loans.created_at', 'DESC')->paginate(15, 'loans');

        return view('payroll/loans/index', ['title' => 'Loans', 'loans' => $loans, 'pager' => $model->pager, 'filters' => ['status' => $status]]);
    }

    public function create()
    {
        return view('payroll/loans/form', ['title' => 'Add Loan']);
    }

    public function store()
    {
        $post = $this->request->getPost();

        try {
            (new PayrollLoanService())->create([
                'employee_id'      => (int) $post['employee_id'],
                'loan_number'      => (string) $post['loan_number'],
                'loan_type'        => (string) $post['loan_type'],
                'principal_amount' => (float) $post['principal_amount'],
                'interest_rate'    => (float) ($post['interest_rate'] ?? 0),
                'tenure_months'    => (int) $post['tenure_months'],
                'emi_amount'       => (float) $post['emi_amount'],
                'start_month'      => (int) $post['start_month'],
                'start_year'       => (int) $post['start_year'],
                'status'           => 'active',
            ]);
        } catch (RuntimeException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('payroll/loans'))->with('success', 'Loan created and installment schedule generated.');
    }

    public function installments($id)
    {
        $loan = (new PayrollLoanModel(service('tenantContext')->db()))->withEmployee()->find($id);
        if (! $loan) {
            return redirect()->to(site_url('payroll/loans'))->with('error', 'Loan not found.');
        }

        return view('payroll/loans/installments', [
            'title'        => 'Loan Installments',
            'loan'         => $loan,
            'installments' => (new PayrollLoanService())->installments((int) $id),
        ]);
    }

    public function foreclose($id)
    {
        try {
            (new PayrollLoanService())->foreclose((int) $id);
        } catch (RuntimeException $e) {
            return redirect()->to(site_url('payroll/loans'))->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('payroll/loans'))->with('success', 'Loan foreclosed.');
    }
}
