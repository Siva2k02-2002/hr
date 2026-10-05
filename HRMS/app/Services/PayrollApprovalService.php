<?php

namespace App\Services;

use App\Models\PayrollArrearsModel;
use App\Models\PayrollBonusModel;
use App\Models\PayrollIncentiveModel;
use App\Models\PayrollMonthModel;
use App\Models\PayrollReimbursementModel;
use App\Models\PayrollRunItemModel;
use App\Models\PayrollRunModel;
use RuntimeException;

/**
 * The single payroll.approve-gated action — the spec's "HR Manager -> Company
 * Admin" two-hop workflow maps to this one action, called once per approver
 * (RBAC already scopes who holds the permission; see the Phase 7 plan).
 * This is also where every previously-read-only loan installment / advance
 * recovery / bonus / incentive / reimbursement / arrears actually commits —
 * a draft run never mutates any of those tables, only this approve step does.
 */
class PayrollApprovalService
{
    public function __construct(
        private AuditService $audit = new AuditService(),
        private PayrollLoanService $loanService = new PayrollLoanService(),
        private PayrollAdvanceService $advanceService = new PayrollAdvanceService()
    ) {
    }

    public function approve(int $runId, int $approvedBy): void
    {
        $db  = service('tenantContext')->db();
        $run = (new PayrollRunModel($db))->find($runId);
        if (! $run) {
            throw new RuntimeException('Payroll run not found.');
        }
        if ($run['status'] !== 'generated') {
            throw new RuntimeException('Only a generated run can be approved.');
        }

        $items = (new PayrollRunItemModel($db))->forRun($runId);
        foreach ($items as $item) {
            $this->commitItem($db, $item);
        }

        $now = date('Y-m-d H:i:s');
        (new PayrollRunModel($db))->update($runId, ['status' => 'approved', 'approved_by' => $approvedBy, 'approved_at' => $now]);
        (new PayrollMonthModel($db))->update($run['payroll_month_id'], ['status' => 'approved', 'approved_by' => $approvedBy, 'approved_at' => $now]);
        (new PayrollRunItemModel($db))->where('payroll_run_id', $runId)->set(['status' => 'approved'])->update();

        $this->audit->log('payroll_approve', 'payroll', 'payroll_run', $runId, $run, ['status' => 'approved', 'approved_by' => $approvedBy]);
    }

    private function commitItem($db, array $item): void
    {
        $earnings   = json_decode((string) $item['earnings_breakdown'], true) ?: [];
        $deductions = json_decode((string) $item['deductions_breakdown'], true) ?: [];

        $sourceIds = $earnings['source_ids'] ?? [];
        $this->markPaid(new PayrollBonusModel($db), $sourceIds['bonus'] ?? [], (int) $item['payroll_month_id']);
        $this->markPaid(new PayrollIncentiveModel($db), $sourceIds['incentive'] ?? [], (int) $item['payroll_month_id']);
        $this->markPaid(new PayrollArrearsModel($db), $sourceIds['arrears'] ?? [], (int) $item['payroll_month_id']);
        $this->markReimbursementsPaid(new PayrollReimbursementModel($db), $sourceIds['reimbursement'] ?? [], (int) $item['payroll_month_id']);

        $deductionIds = $deductions['source_ids'] ?? [];
        foreach ($deductionIds['loan_installments'] ?? [] as $installmentId) {
            $this->loanService->commitInstallment((int) $installmentId, (int) $item['id']);
        }
        foreach ($deductionIds['advances'] ?? [] as $advanceId => $amount) {
            $this->advanceService->commitDeduction((int) $advanceId, (float) $amount);
        }
    }

    private function markPaid($model, array $ids, int $payrollMonthId): void
    {
        if ($ids === []) {
            return;
        }
        $model->whereIn('id', $ids)->set(['status' => 'paid', 'payroll_month_id' => $payrollMonthId])->update();
    }

    private function markReimbursementsPaid(PayrollReimbursementModel $model, array $ids, int $payrollMonthId): void
    {
        if ($ids === []) {
            return;
        }
        $model->whereIn('id', $ids)->set(['status' => 'paid', 'payroll_month_id' => $payrollMonthId])->update();
    }
}
