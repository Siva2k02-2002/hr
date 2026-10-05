<?php

namespace App\Services;

use App\Models\EmployeeLeaveBalanceModel;
use App\Models\EmployeeModel;
use App\Models\LeaveCarryForwardHistoryModel;
use App\Models\LeavePolicyModel;
use App\Models\LeavePolicyRuleModel;
use App\Models\LeaveSettingModel;
use App\Models\LeaveTypeModel;
use RuntimeException;

/**
 * Year-end carry-forward run. Entirely gated by leave_settings.carry_forward_enabled;
 * per employee+type, caps the current closing_balance at the resolved policy-rule
 * limit (or the leave_settings fallback), credits the capped amount into next
 * year's balance row as carry_forward_in, and leaves this year's own
 * closing_balance untouched (carry_forward_out is informational only). No
 * payroll logic — see the Phase 6 plan.
 */
class LeaveCarryForwardService
{
    public function __construct(
        private AuditService $audit = new AuditService(),
        private LeaveBalanceService $balances = new LeaveBalanceService()
    ) {
    }

    /** @return array{batchId: string, processed: int} */
    public function run(int $fromFinancialYear, ?int $actingUserId = null): array
    {
        $db       = service('tenantContext')->db();
        $settings = (new LeaveSettingModel($db))->current();
        if (! $settings['carry_forward_enabled']) {
            throw new RuntimeException('Carry forward is disabled in leave settings.');
        }

        $toFinancialYear = $fromFinancialYear + 1;
        $batchId         = 'CF-' . $fromFinancialYear . '-' . bin2hex(random_bytes(4));
        $historyModel    = new LeaveCarryForwardHistoryModel($db);

        // Every insert below is a delta credit against next year's balance, not an
        // idempotent upsert — re-running for a year already processed would double
        // (or triple...) every employee's carried-forward days with no way to tell
        // from the balance row alone that it happened.
        if ($historyModel->where('from_financial_year', $fromFinancialYear)->countAllResults() > 0) {
            throw new RuntimeException("Carry forward has already been run for FY {$fromFinancialYear}.");
        }

        $employees = (new EmployeeModel($db))->select('id, branch_id, department_id, designation_id, employment_type')->where('status', 'active')->findAll();
        $types     = (new LeaveTypeModel($db))->where('status', 'active')->where('carry_forward_allowed', 1)->findAll();

        $expiryDate = null;
        if (! empty($settings['carry_forward_expiry_month'])) {
            $expiryDate = sprintf('%04d-%02d-%02d', $toFinancialYear, (int) $settings['carry_forward_expiry_month'], 1);
            $expiryDate = date('Y-m-t', strtotime($expiryDate));
        }

        $processed = 0;
        foreach ($employees as $employee) {
            $policy = (new LeavePolicyModel($db))->resolveFor($employee);

            foreach ($types as $type) {
                $rule = $policy ? (new LeavePolicyRuleModel($db))->forPolicyAndType((int) $policy['id'], (int) $type['id']) : null;
                if ($rule !== null && ! $rule['carry_forward_allowed']) {
                    continue;
                }

                $balance = $this->balances->resolveOrCreate((int) $employee['id'], (int) $type['id'], $fromFinancialYear, $rule, $policy['id'] ?? null);
                $eligible = max(0.0, (float) $balance['closing_balance']);
                if ($eligible <= 0) {
                    continue;
                }

                $unlimited = $rule ? (bool) $rule['carry_forward_unlimited'] : false;
                $limit     = $rule['carry_forward_limit'] ?? $settings['carry_forward_limit'];
                $cap       = $unlimited ? $eligible : min($eligible, (float) $limit);

                if ($cap <= 0) {
                    continue;
                }

                $historyModel->insert([
                    'employee_id' => $employee['id'], 'leave_type_id' => $type['id'],
                    'from_financial_year' => $fromFinancialYear, 'to_financial_year' => $toFinancialYear,
                    'eligible_balance' => $eligible, 'carried_forward_days' => $cap, 'expired_days' => max(0, $eligible - $cap),
                    'carry_forward_rule_limit' => $unlimited ? null : $limit, 'expiry_date' => $expiryDate,
                    'processed_by' => $actingUserId, 'run_batch_id' => $batchId, 'created_at' => date('Y-m-d H:i:s'),
                ]);

                $nextYearBalance = $this->balances->resolveOrCreate((int) $employee['id'], (int) $type['id'], $toFinancialYear, $rule, $policy['id'] ?? null);
                $this->creditCarryForward((int) $nextYearBalance['id'], $cap);
                $this->debitCarryForwardOut((int) $balance['id'], $cap);

                $processed++;
            }
        }

        $this->audit->log('leave_carry_forward_run', 'leave', 'leave_carry_forward_history', null, null, [
            'batch_id' => $batchId, 'from' => $fromFinancialYear, 'to' => $toFinancialYear, 'processed' => $processed,
        ]);

        return ['batchId' => $batchId, 'processed' => $processed];
    }

    private function creditCarryForward(int $balanceId, float $amount): void
    {
        $model = new EmployeeLeaveBalanceModel(service('tenantContext')->db());
        $row   = $model->find($balanceId);
        $model->update($balanceId, [
            'carry_forward_in' => (float) $row['carry_forward_in'] + $amount,
            'closing_balance'  => (float) $row['closing_balance'] + $amount,
            'last_transaction_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /** Informational only on the *source* year's row — does not reduce that year's own closing_balance. */
    private function debitCarryForwardOut(int $balanceId, float $amount): void
    {
        $model = new EmployeeLeaveBalanceModel(service('tenantContext')->db());
        $row   = $model->find($balanceId);
        $model->update($balanceId, [
            'carry_forward_out' => (float) $row['carry_forward_out'] + $amount,
        ]);
    }
}
