<?php

namespace App\Controllers;

use App\Models\PayrollReimbursementModel;
use App\Services\PayrollReimbursementService;
use CodeIgniter\Exceptions\PageNotFoundException;
use RuntimeException;

class PayrollReimbursementsController extends BaseController
{
    public function index()
    {
        $status = (string) $this->request->getGet('status');
        $model  = (new PayrollReimbursementModel(service('tenantContext')->db()))->withEmployee();
        if ($status !== '') {
            $model->where('payroll_reimbursements.status', $status);
        }

        $reimbursements = $model->orderBy('payroll_reimbursements.expense_date', 'DESC')->paginate(15, 'reimbursements');

        return view('payroll/reimbursements/index', ['title' => 'Reimbursements', 'reimbursements' => $reimbursements, 'pager' => $model->pager, 'filters' => ['status' => $status]]);
    }

    public function create()
    {
        return view('payroll/reimbursements/form', ['title' => 'Add Reimbursement']);
    }

    public function store()
    {
        $post = $this->request->getPost();

        try {
            (new PayrollReimbursementService())->create([
                'employee_id'  => (int) $post['employee_id'],
                'expense_type' => (string) $post['expense_type'],
                'amount'       => (float) $post['amount'],
                'expense_date' => (string) $post['expense_date'],
                'remarks'      => $post['remarks'] ?: null,
                'status'       => 'pending',
            ], $this->request->getFile('attachment'));
        } catch (RuntimeException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('payroll/reimbursements'))->with('success', 'Reimbursement submitted.');
    }

    public function approve($id)
    {
        try {
            (new PayrollReimbursementService())->approve((int) $id, (int) session('tenant_user_id'));
        } catch (RuntimeException $e) {
            return redirect()->to(site_url('payroll/reimbursements'))->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('payroll/reimbursements'))->with('success', 'Reimbursement approved.');
    }

    public function reject($id)
    {
        try {
            (new PayrollReimbursementService())->reject((int) $id, (int) session('tenant_user_id'), (string) $this->request->getPost('remarks') ?: null);
        } catch (RuntimeException $e) {
            return redirect()->to(site_url('payroll/reimbursements'))->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('payroll/reimbursements'))->with('success', 'Reimbursement rejected.');
    }

    public function download($id)
    {
        $path = (new PayrollReimbursementService())->attachmentPath((int) $id);
        if (! $path || ! is_file($path)) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $this->response->download($path, null);
    }
}
