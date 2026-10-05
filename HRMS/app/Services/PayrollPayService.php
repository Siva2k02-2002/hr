<?php

namespace App\Services;

use App\Models\PayrollMonthModel;
use App\Models\PayrollRunItemModel;
use App\Models\PayrollRunModel;
use RuntimeException;

class PayrollPayService
{
    public function __construct(
        private AuditService $audit = new AuditService(),
        private NotificationService $notifications = new NotificationService()
    ) {
    }

    public function pay(int $runId, int $paidBy): void
    {
        $db  = service('tenantContext')->db();
        $run = (new PayrollRunModel($db))->find($runId);
        if (! $run) {
            throw new RuntimeException('Payroll run not found.');
        }
        if ($run['status'] !== 'locked') {
            throw new RuntimeException('Only a locked run can be marked as paid.');
        }

        $now = date('Y-m-d H:i:s');
        (new PayrollRunModel($db))->update($runId, ['status' => 'paid', 'paid_by' => $paidBy, 'paid_at' => $now]);
        (new PayrollMonthModel($db))->update($run['payroll_month_id'], ['status' => 'paid', 'paid_at' => $now]);
        (new PayrollRunItemModel($db))->where('payroll_run_id', $runId)->set(['status' => 'paid'])->update();

        $this->audit->log('payroll_paid', 'payroll', 'payroll_run', $runId, $run, ['status' => 'paid', 'paid_by' => $paidBy]);

        $this->notifyPayslipsAvailable($db, $runId, $run['payroll_month_id']);
    }

    private function notifyPayslipsAvailable($db, int $runId, int $payrollMonthId): void
    {
        $month = (new PayrollMonthModel($db))->find($payrollMonthId);
        $period = $month ? date('F Y', strtotime($month['start_date'])) : 'this period';

        $rows = $db->table('payroll_run_items ri')
            ->select('e.user_id, e.company_email, e.personal_email')
            ->join('employees e', 'e.id = ri.employee_id')
            ->where('ri.payroll_run_id', $runId)
            ->where('e.user_id IS NOT NULL')
            ->get()->getResultArray();

        foreach ($rows as $row) {
            $this->notifications->payslipAvailable((int) $row['user_id'], $period, $row['company_email'] ?: $row['personal_email'] ?: null);
        }
    }
}
