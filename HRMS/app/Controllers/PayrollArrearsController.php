<?php

namespace App\Controllers;

use App\Models\PayrollArrearsModel;
use App\Models\PayrollMonthModel;
use App\Services\PayrollArrearsService;
use RuntimeException;

class PayrollArrearsController extends BaseController
{
    public function index()
    {
        $status = (string) $this->request->getGet('status');
        $model  = (new PayrollArrearsModel(service('tenantContext')->db()))->withEmployee();
        if ($status !== '') {
            $model->where('payroll_arrears.status', $status);
        }

        $arrears = $model->orderBy('payroll_arrears.created_at', 'DESC')->paginate(15, 'arrears');

        return view('payroll/arrears/index', ['title' => 'Arrears', 'arrears' => $arrears, 'pager' => $model->pager, 'filters' => ['status' => $status]]);
    }

    public function create()
    {
        return view('payroll/arrears/form', ['title' => 'Add Arrears', 'months' => (new PayrollMonthModel(service('tenantContext')->db()))->orderBy('year', 'DESC')->orderBy('month', 'DESC')->findAll(20)]);
    }

    public function store()
    {
        $post = $this->request->getPost();

        (new PayrollArrearsService())->create([
            'employee_id'      => (int) $post['employee_id'],
            'from_month'       => (int) $post['from_month'],
            'from_year'        => (int) $post['from_year'],
            'to_month'         => (int) $post['to_month'],
            'to_year'          => (int) $post['to_year'],
            'amount'           => (float) $post['amount'],
            'reason'           => $post['reason'] ?: null,
            'payroll_month_id' => $post['payroll_month_id'] !== '' ? (int) $post['payroll_month_id'] : null,
            'status'           => 'pending',
        ]);

        return redirect()->to(site_url('payroll/arrears'))->with('success', 'Arrears recorded.');
    }

    public function delete($id)
    {
        try {
            (new PayrollArrearsService())->delete((int) $id);
        } catch (RuntimeException $e) {
            return redirect()->to(site_url('payroll/arrears'))->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('payroll/arrears'))->with('success', 'Arrears entry deleted.');
    }
}
