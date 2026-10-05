<?php

namespace App\Controllers;

use App\Models\LeaveCarryForwardHistoryModel;
use App\Services\LeaveCarryForwardService;
use RuntimeException;

class LeaveCarryForwardController extends BaseController
{
    public function index()
    {
        $currentFy = leave_financial_year();

        return view('leave/carry_forward/index', [
            'title'     => 'Carry Forward',
            'batches'   => (new LeaveCarryForwardHistoryModel(service('tenantContext')->db()))->recentBatches(),
            'currentFy' => $currentFy,
        ]);
    }

    public function run()
    {
        $fromYear = (int) $this->request->getPost('from_financial_year');
        if ($fromYear <= 0) {
            return redirect()->to(site_url('leave/carry-forward'))->with('error', 'Select a valid financial year to run carry forward from.');
        }

        try {
            $result = (new LeaveCarryForwardService())->run($fromYear, (int) session('tenant_user_id'));
        } catch (RuntimeException $e) {
            return redirect()->to(site_url('leave/carry-forward'))->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('leave/carry-forward'))->with('success', "Carry forward run complete — {$result['processed']} balance row(s) processed.");
    }
}
