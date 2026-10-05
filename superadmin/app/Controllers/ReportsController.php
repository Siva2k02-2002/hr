<?php

namespace App\Controllers;

class ReportsController extends BaseController
{
    /**
     * Placeholder — full cross-module reporting is a later phase
     * (see Phase 10 in the architecture doc). This keeps the menu item
     * and permission gate wired up without pretending the feature exists.
     */
    public function index()
    {
        return view('reports/index', ['title' => 'Reports']);
    }
}
