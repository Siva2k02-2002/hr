<?php

namespace App\Controllers;

use App\Models\CompanyModel;
use App\Models\SubscriptionModel;

class DashboardController extends BaseController
{
    public function index()
    {
        $companies = new CompanyModel();
        $counts    = $companies->counts();

        // Only queried when the viewer actually has subscription.view — the
        // dashboard must not surface subscription/company data through a
        // panel a user couldn't otherwise reach.
        $expiring = can('subscription.view') ? (new SubscriptionModel())->expiringWithin(14) : null;

        return view('dashboard/index', [
            'title'    => 'Dashboard',
            'counts'   => $counts,
            'total'    => array_sum($counts),
            'expiring' => $expiring,
        ]);
    }
}
