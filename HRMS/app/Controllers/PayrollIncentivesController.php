<?php

namespace App\Controllers;

use App\Models\PayrollIncentiveModel;
use App\Models\PayrollMonthModel;
use App\Services\PayrollIncentiveService;
use RuntimeException;

class PayrollIncentivesController extends BaseController
{
    public function index()
    {
        $status = (string) $this->request->getGet('status');
        $model  = (new PayrollIncentiveModel(service('tenantContext')->db()))->withEmployee();
        if ($status !== '') {
            $model->where('payroll_incentives.status', $status);
        }

        $incentives = $model->orderBy('payroll_incentives.created_at', 'DESC')->paginate(15, 'incentives');

        return view('payroll/incentives/index', ['title' => 'Incentives', 'incentives' => $incentives, 'pager' => $model->pager, 'filters' => ['status' => $status]]);
    }

    public function create()
    {
        return view('payroll/incentives/form', ['title' => 'Add Incentive', 'months' => (new PayrollMonthModel(service('tenantContext')->db()))->orderBy('year', 'DESC')->orderBy('month', 'DESC')->findAll(20)]);
    }

    public function store()
    {
        $post = $this->request->getPost();

        (new PayrollIncentiveService())->create([
            'employee_id'      => (int) $post['employee_id'],
            'incentive_type'   => (string) $post['incentive_type'],
            'amount'           => (float) $post['amount'],
            'payroll_month_id' => $post['payroll_month_id'] !== '' ? (int) $post['payroll_month_id'] : null,
            'remarks'          => $post['remarks'] ?: null,
            'status'           => 'pending',
        ]);

        return redirect()->to(site_url('payroll/incentives'))->with('success', 'Incentive recorded.');
    }

    public function delete($id)
    {
        try {
            (new PayrollIncentiveService())->delete((int) $id);
        } catch (RuntimeException $e) {
            return redirect()->to(site_url('payroll/incentives'))->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('payroll/incentives'))->with('success', 'Incentive deleted.');
    }
}
