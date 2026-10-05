<?php

namespace App\Controllers;

use App\Models\PayrollAdvanceModel;
use App\Services\PayrollAdvanceService;
use RuntimeException;

class PayrollAdvancesController extends BaseController
{
    public function index()
    {
        $status = (string) $this->request->getGet('status');
        $model  = (new PayrollAdvanceModel(service('tenantContext')->db()))->withEmployee();
        if ($status !== '') {
            $model->where('payroll_advances.status', $status);
        }

        $advances = $model->orderBy('payroll_advances.advance_date', 'DESC')->paginate(15, 'advances');

        return view('payroll/advances/index', ['title' => 'Salary Advances', 'advances' => $advances, 'pager' => $model->pager, 'filters' => ['status' => $status]]);
    }

    public function create()
    {
        return view('payroll/advances/form', ['title' => 'Add Salary Advance']);
    }

    public function store()
    {
        $post = $this->request->getPost();
        $recoveryType = $post['recovery_type'] ?? 'installments';
        $amount       = (float) $post['amount'];
        $installments = max(1, (int) ($post['installments_count'] ?? 1));

        (new PayrollAdvanceService())->create([
            'employee_id'         => (int) $post['employee_id'],
            'amount'              => $amount,
            'advance_date'        => (string) $post['advance_date'],
            'recovery_type'       => $recoveryType,
            'installments_count'  => $recoveryType === 'lump_sum' ? 1 : $installments,
            'installment_amount'  => $recoveryType === 'lump_sum' ? $amount : round($amount / $installments, 2),
            'reason'              => $post['reason'] ?: null,
            'status'              => 'active',
        ]);

        return redirect()->to(site_url('payroll/advances'))->with('success', 'Salary advance recorded.');
    }

    public function close($id)
    {
        try {
            (new PayrollAdvanceService())->close((int) $id);
        } catch (RuntimeException $e) {
            return redirect()->to(site_url('payroll/advances'))->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('payroll/advances'))->with('success', 'Advance closed.');
    }
}
