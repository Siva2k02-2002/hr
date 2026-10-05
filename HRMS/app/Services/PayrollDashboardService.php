<?php

namespace App\Services;

use App\Models\PayrollRunModel;

class PayrollDashboardService
{
    /** @return array{latest: ?array, pending_approval: int, paid_runs: int} */
    public function stats(): array
    {
        $db    = service('tenantContext')->db();
        $model = new PayrollRunModel($db);

        $latest = $model->withMonth()->where('payroll_runs.status !=', 'cancelled')->orderBy('m.year', 'DESC')->orderBy('m.month', 'DESC')->first();

        return [
            'latest'           => $latest,
            'pending_approval' => (new PayrollRunModel($db))->where('status', 'generated')->countAllResults(),
            'paid_runs'        => (new PayrollRunModel($db))->where('status', 'paid')->countAllResults(),
        ];
    }
}
