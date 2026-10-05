<?php

namespace App\Controllers;

use App\Models\PayrollRunItemModel;
use App\Models\PayrollRunModel;
use App\Services\PayrollApprovalService;
use App\Services\PayrollLockService;
use App\Services\PayrollPayService;
use App\Services\PayrollRunService;
use RuntimeException;

class PayrollRunsController extends BaseController
{
    public function index()
    {
        $model = (new PayrollRunModel(service('tenantContext')->db()))->withMonth();
        $runs  = $model->orderBy('m.year', 'DESC')->orderBy('m.month', 'DESC')->paginate(15, 'runs');

        return view('payroll/runs/index', ['title' => 'Payroll Runs', 'runs' => $runs, 'pager' => $model->pager]);
    }

    public function generateForm()
    {
        return view('payroll/runs/generate', ['title' => 'Generate Payroll']);
    }

    public function generate()
    {
        $month = (int) $this->request->getPost('month');
        $year  = (int) $this->request->getPost('year');

        if ($this->rateLimited('payroll_generate', 5, MINUTE)) {
            return redirect()->to(site_url('payroll/runs/generate'))->with('error', 'Too many payroll generation attempts. Please slow down.');
        }

        try {
            $result = (new PayrollRunService())->generate($month, $year);
        } catch (RuntimeException $e) {
            return redirect()->to(site_url('payroll/runs/generate'))->with('error', $e->getMessage());
        }

        $message = "Payroll generated for {$result['processed']} employee(s).";
        if (! empty($result['skipped'])) {
            $message .= ' ' . count($result['skipped']) . ' skipped — see run detail for reasons.';
        }

        return redirect()->to(site_url('payroll/runs/' . $result['run']['id']))->with('success', $message);
    }

    public function show($id)
    {
        $run = (new PayrollRunModel(service('tenantContext')->db()))->withMonth()->find($id);
        if (! $run) {
            return redirect()->to(site_url('payroll/runs'))->with('error', 'Payroll run not found.');
        }

        return view('payroll/runs/show', [
            'title' => 'Payroll Run — ' . payroll_period_label((int) $run['month'], (int) $run['year']),
            'run'   => $run,
            'items' => (new PayrollRunItemModel(service('tenantContext')->db()))->forRun((int) $id),
        ]);
    }

    public function approve($id)
    {
        try {
            (new PayrollApprovalService())->approve((int) $id, (int) session('tenant_user_id'));
        } catch (RuntimeException $e) {
            return redirect()->to(site_url('payroll/runs/' . $id))->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('payroll/runs/' . $id))->with('success', 'Payroll run approved.');
    }

    public function lock($id)
    {
        try {
            (new PayrollLockService())->lock((int) $id, (int) session('tenant_user_id'));
        } catch (RuntimeException $e) {
            return redirect()->to(site_url('payroll/runs/' . $id))->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('payroll/runs/' . $id))->with('success', 'Payroll run locked.');
    }

    public function unlock($id)
    {
        $reason = (string) $this->request->getPost('reason');
        if ($reason === '') {
            return redirect()->to(site_url('payroll/runs/' . $id))->with('error', 'A reason is required to unlock a payroll run.');
        }

        try {
            (new PayrollLockService())->unlock((int) $id, (int) session('tenant_user_id'), $reason);
        } catch (RuntimeException $e) {
            return redirect()->to(site_url('payroll/runs/' . $id))->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('payroll/runs/' . $id))->with('success', 'Payroll run unlocked.');
    }

    public function pay($id)
    {
        try {
            (new PayrollPayService())->pay((int) $id, (int) session('tenant_user_id'));
        } catch (RuntimeException $e) {
            return redirect()->to(site_url('payroll/runs/' . $id))->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('payroll/runs/' . $id))->with('success', 'Payroll run marked as paid.');
    }

    public function cancel($id)
    {
        try {
            (new PayrollRunService())->cancel((int) $id);
        } catch (RuntimeException $e) {
            return redirect()->to(site_url('payroll/runs/' . $id))->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('payroll/runs'))->with('success', 'Payroll run cancelled.');
    }
}
