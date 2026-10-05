<?php

namespace App\Controllers;

use App\Services\PayrollDashboardService;

class PayrollDashboardController extends BaseController
{
    public function index()
    {
        return view('payroll/dashboard', ['title' => 'Payroll Dashboard', 'stats' => (new PayrollDashboardService())->stats()]);
    }
}
