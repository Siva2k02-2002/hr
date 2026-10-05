<?php

namespace App\Services;

use App\Models\PayrollMonthModel;
use App\Models\PayrollRunItemModel;
use App\Models\PayrollRunModel;
use RuntimeException;

/** Locking/unlocking a run also locks/unlocks its payroll_month — both flip together, they're never allowed to drift apart. */
class PayrollLockService
{
    public function __construct(private AuditService $audit = new AuditService())
    {
    }

    public function lock(int $runId, int $lockedBy): void
    {
        $db  = service('tenantContext')->db();
        $run = (new PayrollRunModel($db))->find($runId);
        if (! $run) {
            throw new RuntimeException('Payroll run not found.');
        }
        if ($run['status'] !== 'approved') {
            throw new RuntimeException('Only an approved run can be locked.');
        }

        $now = date('Y-m-d H:i:s');
        (new PayrollRunModel($db))->update($runId, ['status' => 'locked', 'locked_by' => $lockedBy, 'locked_at' => $now]);
        (new PayrollMonthModel($db))->update($run['payroll_month_id'], ['status' => 'locked', 'locked_by' => $lockedBy, 'locked_at' => $now]);
        (new PayrollRunItemModel($db))->where('payroll_run_id', $runId)->set(['status' => 'locked'])->update();

        $this->audit->log('payroll_lock', 'payroll', 'payroll_run', $runId, $run, ['status' => 'locked', 'locked_by' => $lockedBy]);
    }

    /** Requires payroll.lock permission at the controller/route level — unlock is deliberately gated by the same permission as lock, not a lesser one. $reason is mandatory and always audit-logged. */
    public function unlock(int $runId, int $unlockedBy, string $reason): void
    {
        $db  = service('tenantContext')->db();
        $run = (new PayrollRunModel($db))->find($runId);
        if (! $run) {
            throw new RuntimeException('Payroll run not found.');
        }
        if ($run['status'] !== 'locked') {
            throw new RuntimeException('Only a locked run can be unlocked.');
        }

        (new PayrollRunModel($db))->update($runId, ['status' => 'approved', 'locked_by' => null, 'locked_at' => null]);
        (new PayrollMonthModel($db))->update($run['payroll_month_id'], ['status' => 'approved', 'locked_by' => null, 'locked_at' => null]);
        (new PayrollRunItemModel($db))->where('payroll_run_id', $runId)->set(['status' => 'approved'])->update();

        $this->audit->log('payroll_unlock', 'payroll', 'payroll_run', $runId, $run, ['status' => 'approved', 'unlocked_by' => $unlockedBy, 'reason' => $reason]);
    }
}
