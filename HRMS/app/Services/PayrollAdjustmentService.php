<?php

namespace App\Services;

use App\Models\PayrollAdjustmentModel;
use App\Models\PayrollRunItemModel;
use App\Models\PayrollRunModel;
use RuntimeException;

/**
 * Ad-hoc pre-approval corrections to one payroll_run_items row — also how
 * TDS gets manually overridden (a 'deduction' adjustment labelled "TDS
 * override"). Adjustments are stored and displayed separately from the
 * computed earnings/deductions breakdown JSON; they only ever adjust the
 * run item's totals, never rewrite the computed breakdown itself.
 */
class PayrollAdjustmentService
{
    public function __construct(private AuditService $audit = new AuditService())
    {
    }

    public function add(int $runItemId, string $type, string $label, float $amount, ?string $reason): int
    {
        $db       = service('tenantContext')->db();
        $itemModel = new PayrollRunItemModel($db);
        $item      = $itemModel->find($runItemId);
        if (! $item) {
            throw new RuntimeException('Payroll line not found.');
        }

        $run = (new PayrollRunModel($db))->find($item['payroll_run_id']);
        if (! in_array($run['status'], ['draft', 'generated'], true)) {
            throw new RuntimeException('Adjustments can only be made before a payroll run is approved.');
        }

        $id = (new PayrollAdjustmentModel($db))->insert([
            'payroll_run_item_id' => $runItemId,
            'type'                => $type,
            'label'               => $label,
            'amount'              => $amount,
            'reason'              => $reason,
            'created_by'          => session('tenant_user_id'),
            'created_at'          => date('Y-m-d H:i:s'),
        ], true);

        $grossEarnings   = (float) $item['gross_earnings'] + ($type === 'earning' ? $amount : 0);
        $grossDeductions = (float) $item['gross_deductions'] + ($type === 'deduction' ? $amount : 0);

        $itemModel->update($runItemId, [
            'gross_earnings'   => round($grossEarnings, 2),
            'gross_deductions' => round($grossDeductions, 2),
            'net_salary'       => max(0, round($grossEarnings - $grossDeductions, 2)),
        ]);

        $this->audit->log('payroll_adjustment_add', 'payroll', 'payroll_run_item', $runItemId, $item, ['type' => $type, 'label' => $label, 'amount' => $amount]);

        return $id;
    }

    public function delete(int $adjustmentId): void
    {
        $db        = service('tenantContext')->db();
        $adjModel  = new PayrollAdjustmentModel($db);
        $adjustment = $adjModel->find($adjustmentId);
        if (! $adjustment) {
            throw new RuntimeException('Adjustment not found.');
        }

        $itemModel = new PayrollRunItemModel($db);
        $item      = $itemModel->find($adjustment['payroll_run_item_id']);
        $run       = (new PayrollRunModel($db))->find($item['payroll_run_id']);
        if (! in_array($run['status'], ['draft', 'generated'], true)) {
            throw new RuntimeException('Adjustments can only be removed before a payroll run is approved.');
        }

        $grossEarnings   = (float) $item['gross_earnings'] - ($adjustment['type'] === 'earning' ? (float) $adjustment['amount'] : 0);
        $grossDeductions = (float) $item['gross_deductions'] - ($adjustment['type'] === 'deduction' ? (float) $adjustment['amount'] : 0);

        $itemModel->update($item['id'], [
            'gross_earnings'   => round($grossEarnings, 2),
            'gross_deductions' => round($grossDeductions, 2),
            'net_salary'       => max(0, round($grossEarnings - $grossDeductions, 2)),
        ]);

        $adjModel->delete($adjustmentId);
        $this->audit->log('payroll_adjustment_delete', 'payroll', 'payroll_run_item', $item['id'], $adjustment, null);
    }
}
