<?php

namespace App\Services;

use App\Models\EmployeeLeaveBalanceModel;
use App\Models\LeaveApplicationModel;
use App\Models\LeavePolicyModel;
use App\Models\LeavePolicyRuleModel;
use App\Models\LeaveTypeModel;
use RuntimeException;

/**
 * Owns the balance ledger. Every method mutates the stored `closing_balance`
 * by delta arithmetic — this service never recomputes a balance by SUMing
 * historical applications.
 *
 * Simplification vs. the original plan: `accrual_method`/`monthly_accrual_days`
 * on leave_policy_rules are persisted and shown in the UI, but the full
 * annual_allocation is credited to `earned` at balance-row creation
 * regardless of accrual_method — a live month-by-month accrual catch-up
 * engine is payroll-adjacent precision this phase's spec explicitly excludes
 * ("Do NOT build Payroll... calculations"), and building one correctly
 * (calendar-month boundaries, mid-year joiners, partial months) is
 * disproportionate risk for a field the spec only names in one example line.
 * `monthly_accrual_days` remains available for Phase 7+ to use.
 */
class LeaveBalanceService
{
    private EmployeeLeaveBalanceModel $balances;

    public function __construct(private AuditService $audit = new AuditService())
    {
        $this->balances = new EmployeeLeaveBalanceModel(service('tenantContext')->db());
    }

    public function resolveOrCreate(int $employeeId, int $leaveTypeId, int $financialYear, ?array $policyRule = null, ?int $leavePolicyId = null): array
    {
        $row = $this->balances->forEmployeeTypeYear($employeeId, $leaveTypeId, $financialYear);
        if ($row) {
            return $row;
        }

        $earned = (float) ($policyRule['annual_allocation'] ?? 0);
        $id     = $this->balances->insert([
            'employee_id' => $employeeId, 'leave_type_id' => $leaveTypeId, 'leave_policy_id' => $leavePolicyId,
            'financial_year' => $financialYear, 'opening_balance' => 0, 'earned' => $earned, 'availed' => 0,
            'adjusted' => 0, 'carry_forward_in' => 0, 'carry_forward_out' => 0, 'encashed' => 0,
            'closing_balance' => $earned, 'last_transaction_at' => date('Y-m-d H:i:s'),
        ], true);

        return $this->balances->find($id);
    }

    /** Every active leave type's balance row for this employee+FY, lazily creating any missing ones — powers the My Leave / HR balance list screens. */
    public function resolveOrCreateAllVisible(array $employee, int $financialYear): array
    {
        $db     = service('tenantContext')->db();
        $types  = (new LeaveTypeModel($db))->where('status', 'active')->orderBy('sort_order')->findAll();
        $policy = (new LeavePolicyModel($db))->resolveFor($employee);

        $result = [];
        foreach ($types as $type) {
            $policyRule = $policy ? (new LeavePolicyRuleModel($db))->forPolicyAndType((int) $policy['id'], (int) $type['id']) : null;
            $rule       = $policyRule ?? ['annual_allocation' => $type['annual_allocation']];
            $balance    = $this->resolveOrCreate((int) $employee['id'], (int) $type['id'], $financialYear, $rule, $policy['id'] ?? null);

            $result[] = $balance + [
                'leave_type_name' => $type['name'], 'leave_type_code' => $type['code'], 'leave_type_color' => $type['color'],
            ];
        }

        return $result;
    }

    /** Read-only — for the apply-time insufficient-balance check without committing anything. */
    public function projectedBalance(int $employeeId, int $leaveTypeId, int $financialYear, ?array $policyRule = null, ?int $leavePolicyId = null): float
    {
        return (float) $this->resolveOrCreate($employeeId, $leaveTypeId, $financialYear, $policyRule, $leavePolicyId)['closing_balance'];
    }

    /** Skipped entirely for unpaid (LOP-mapped) leave types — they have no allocation to check against. */
    public function assertSufficient(array $leaveType, int $employeeId, int $leaveTypeId, float $requestedDays, int $financialYear, ?array $policyRule, array $settings): void
    {
        if (! (bool) $leaveType['is_paid'] || (bool) $settings['allow_negative_balance']) {
            return;
        }

        $balance = $this->projectedBalance($employeeId, $leaveTypeId, $financialYear, $policyRule);
        if ($balance < $requestedDays) {
            throw new RuntimeException(sprintf('Insufficient leave balance: %.1f available, %.1f requested.', $balance, $requestedDays));
        }
    }

    /**
     * Fires only when an application's status becomes 'approved'. Pessimistic
     * row-lock (stronger than a bare check-then-act guard) because double-
     * deduction on a balance ledger is a worse failure mode than the
     * equivalent race on a status enum elsewhere in this codebase.
     */
    public function deductOnApproval(int $applicationId): void
    {
        $db           = service('tenantContext')->db();
        $applications = new LeaveApplicationModel($db);

        $db->transStart();

        $app = $applications->lockForUpdate($applicationId);
        if (! $app) {
            $db->transComplete();

            throw new RuntimeException('Leave application not found.');
        }
        if ((int) $app['balance_deducted'] === 1) {
            $db->transComplete();

            return; // already applied — idempotent no-op against a retried/duplicate call
        }

        $leaveType = (new LeaveTypeModel($db))->find((int) $app['leave_type_id']);
        if ((bool) ($leaveType['is_paid'] ?? true)) {
            $financialYear = leave_financial_year($app['from_date']);
            $balance       = $this->resolveOrCreate((int) $app['employee_id'], (int) $app['leave_type_id'], $financialYear, null, $app['leave_policy_id'] ? (int) $app['leave_policy_id'] : null);
            $locked        = $this->balances->lockForUpdate((int) $balance['id']);

            $new = [
                'availed'             => (float) $locked['availed'] + (float) $app['total_days'],
                'closing_balance'     => (float) $locked['closing_balance'] - (float) $app['total_days'],
                'last_transaction_at' => date('Y-m-d H:i:s'),
            ];
            $this->balances->update((int) $locked['id'], $new);
            $this->audit->log('leave_balance_deduct', 'leave', 'employee_leave_balance', (int) $locked['id'], $locked, $new);
        }

        $applications->update($applicationId, ['balance_deducted' => 1]);
        $db->transComplete();
    }

    /** Fires on cancelling a previously-approved application. No-op if nothing was ever deducted (still-pending cancel, or LOP type). */
    public function restoreOnCancellation(int $applicationId): void
    {
        $db           = service('tenantContext')->db();
        $applications = new LeaveApplicationModel($db);

        $db->transStart();

        $app = $applications->lockForUpdate($applicationId);
        if (! $app || (int) $app['balance_deducted'] !== 1) {
            $db->transComplete();

            return;
        }

        $financialYear = leave_financial_year($app['from_date']);
        $balance       = $this->resolveOrCreate((int) $app['employee_id'], (int) $app['leave_type_id'], $financialYear, null, $app['leave_policy_id'] ? (int) $app['leave_policy_id'] : null);
        $locked        = $this->balances->lockForUpdate((int) $balance['id']);

        $new = [
            'availed'             => max(0, (float) $locked['availed'] - (float) $app['total_days']),
            'closing_balance'     => (float) $locked['closing_balance'] + (float) $app['total_days'],
            'last_transaction_at' => date('Y-m-d H:i:s'),
        ];
        $this->balances->update((int) $locked['id'], $new);
        $this->audit->log('leave_balance_restore', 'leave', 'employee_leave_balance', (int) $locked['id'], $locked, $new);

        $applications->update($applicationId, ['balance_deducted' => 0]);
        $db->transComplete();
    }

    /**
     * HR Balance Adjustment screen. The ledger table has no reason/effective_date
     * columns (staying within the spec's 11-table schema) — the mandatory audit
     * trail is satisfied entirely via AuditService, which is where reason/
     * effective_date are durably recorded.
     */
    public function adjustManually(int $employeeId, int $leaveTypeId, int $financialYear, float $delta, string $reason, string $effectiveDate, int $actingUserId): array
    {
        $balance = $this->resolveOrCreate($employeeId, $leaveTypeId, $financialYear);
        $new     = [
            'adjusted'            => (float) $balance['adjusted'] + $delta,
            'closing_balance'     => (float) $balance['closing_balance'] + $delta,
            'last_transaction_at' => date('Y-m-d H:i:s'),
        ];
        $this->balances->update((int) $balance['id'], $new);
        $this->audit->log('leave_balance_adjust', 'leave', 'employee_leave_balance', (int) $balance['id'], $balance, $new + [
            'delta' => $delta, 'reason' => $reason, 'effective_date' => $effectiveDate, 'acted_by' => $actingUserId,
        ]);

        return $this->balances->find((int) $balance['id']);
    }

    /**
     * Re-checks the balance at debit time, not just at request time (audit finding LEA-01):
     * an employee can take more leave between requesting an encashment and it being
     * approved, so the balance available when the request was made is no guarantee it's
     * still available now. Row-locked like deductOnApproval()/restoreOnCancellation() so
     * two concurrent approvals can't both read the same starting balance.
     */
    public function debitForEncashment(int $employeeId, int $leaveTypeId, int $financialYear, float $days): array
    {
        $db = service('tenantContext')->db();
        $db->transStart();

        $balance = $this->resolveOrCreate($employeeId, $leaveTypeId, $financialYear);
        $locked  = $this->balances->lockForUpdate((int) $balance['id']);

        if ((float) $locked['closing_balance'] < $days) {
            $db->transComplete();

            throw new RuntimeException(sprintf(
                'Balance has changed since this was requested — %.1f available, %.1f requested. Reject and ask the employee to re-submit.',
                (float) $locked['closing_balance'],
                $days
            ));
        }

        $new = [
            'encashed'            => (float) $locked['encashed'] + $days,
            'closing_balance'     => (float) $locked['closing_balance'] - $days,
            'last_transaction_at' => date('Y-m-d H:i:s'),
        ];
        $this->balances->update((int) $locked['id'], $new);
        $this->audit->log('leave_balance_encash', 'leave', 'employee_leave_balance', (int) $locked['id'], $locked, $new);

        $db->transComplete();
        if ($db->transStatus() === false) {
            throw new RuntimeException('Could not process the encashment — please try again.');
        }

        return $this->balances->find((int) $locked['id']);
    }
}
