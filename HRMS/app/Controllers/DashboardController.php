<?php

namespace App\Controllers;

use App\Models\EmployeeModel;
use App\Models\UserModel;
use App\Services\DashboardService;

class DashboardController extends BaseController
{
    public function index()
    {
        $dashboard = new DashboardService();
        $data      = ['title' => 'Dashboard'];

        // Company-wide widgets (including the tenant-wide user count) are only for
        // viewers who hold at least one company-wide admin permission — a Manager sees
        // attendance/leave widgets (they approve those) but not payroll ones, matching
        // what the sidebar already lets them navigate to. A plain Employee holds only
        // the .own self-service variants of attendance.view/leave.view, so none of
        // these checks are true for them and no company-wide data is computed at all.
        if (can('employee.view') || can('attendance.view') || can('leave.view') || can('payroll.view')) {
            $data['userCount'] = (new UserModel(service('tenantContext')->db()))->countAllResults();
            $data['company']  = $dashboard->companyWide();
        }

        // Subscription/Trial status is company account/billing information, not
        // something a plain Employee needs — gated by settings.view (the same
        // permission that already governs the Company Settings screen this data
        // belongs to), so it's simply never computed or sent to the view for them.
        if (can('settings.view')) {
            $data['subscriptionStatus'] = tenant()->subscriptionStatus();
        }

        $employee = (new EmployeeModel(service('tenantContext')->db()))->where('user_id', session('tenant_user_id'))->first();
        if ($employee) {
            $data['employee'] = $dashboard->forEmployee((int) $employee['id']);
        }

        return view('dashboard/index', $data);
    }
}
