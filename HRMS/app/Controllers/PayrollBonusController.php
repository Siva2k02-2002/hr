<?php

namespace App\Controllers;

use App\Models\PayrollBonusModel;
use App\Models\PayrollMonthModel;
use App\Services\PayrollBonusService;
use RuntimeException;

class PayrollBonusController extends BaseController
{
    public function index()
    {
        $status = (string) $this->request->getGet('status');
        $model  = (new PayrollBonusModel(service('tenantContext')->db()))->withEmployee();
        if ($status !== '') {
            $model->where('payroll_bonus.status', $status);
        }

        $bonuses = $model->orderBy('payroll_bonus.created_at', 'DESC')->paginate(15, 'bonuses');

        return view('payroll/bonus/index', ['title' => 'Bonus', 'bonuses' => $bonuses, 'pager' => $model->pager, 'filters' => ['status' => $status]]);
    }

    public function create()
    {
        return view('payroll/bonus/form', ['title' => 'Add Bonus', 'months' => (new PayrollMonthModel(service('tenantContext')->db()))->orderBy('year', 'DESC')->orderBy('month', 'DESC')->findAll(20)]);
    }

    public function store()
    {
        $post        = $this->request->getPost();
        $employeeIds = $post['employee_ids'] ?? [];

        if ($employeeIds === []) {
            return redirect()->back()->withInput()->with('error', 'Select at least one employee.');
        }

        $created = (new PayrollBonusService())->applyToEmployees(
            $employeeIds,
            (string) $post['bonus_type'],
            (float) $post['amount'],
            $post['payroll_month_id'] !== '' ? (int) $post['payroll_month_id'] : null,
            $post['remarks'] ?: null
        );

        $skipped = count($employeeIds) - count($created);
        $message = 'Bonus applied to ' . count($created) . ' employee(s).' . ($skipped > 0 ? " {$skipped} skipped as duplicates." : '');

        return redirect()->to(site_url('payroll/bonus'))->with('success', $message);
    }

    public function delete($id)
    {
        try {
            (new PayrollBonusService())->delete((int) $id);
        } catch (RuntimeException $e) {
            return redirect()->to(site_url('payroll/bonus'))->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('payroll/bonus'))->with('success', 'Bonus entry deleted.');
    }
}
