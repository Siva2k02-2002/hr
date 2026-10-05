<?php

namespace App\Services;

use App\Models\PayrollAdvanceModel;
use RuntimeException;

/** Balance/recovered mutation only happens at payroll approval time (see PayrollApprovalService), same "commit on approval" precedent as loans and LeaveBalanceService. */
class PayrollAdvanceService
{
    private PayrollAdvanceModel $advances;

    public function __construct(private AuditService $audit = new AuditService())
    {
        $this->advances = new PayrollAdvanceModel(service('tenantContext')->db());
    }

    public function create(array $data): int
    {
        $data['remaining_balance'] = $data['amount'];
        $data['created_by']        = session('tenant_user_id');
        $id                        = $this->advances->insert($data, true);
        $this->audit->log('advance_create', 'payroll', 'payroll_advance', $id, null, $data);

        return $id;
    }

    public function forEmployee(int $employeeId): array
    {
        return $this->advances->forEmployee($employeeId);
    }

    /** Read-only — what PayrollRunService should show as advance_deduction for this employee this period. */
    public function nextDeductionAmount(array $advance): float
    {
        $remaining = (float) $advance['remaining_balance'];
        if ($remaining <= 0) {
            return 0.0;
        }

        return $advance['recovery_type'] === 'lump_sum' ? $remaining : min($remaining, (float) $advance['installment_amount']);
    }

    /** Called only from PayrollApprovalService::approve(). */
    public function commitDeduction(int $advanceId, float $amount): void
    {
        $advance = $this->advances->find($advanceId);
        if (! $advance || $amount <= 0) {
            return;
        }

        $newRemaining = max(0, round((float) $advance['remaining_balance'] - $amount, 2));
        $this->advances->update($advanceId, [
            'recovered_amount'  => round((float) $advance['recovered_amount'] + $amount, 2),
            'remaining_balance' => $newRemaining,
            'status'            => $newRemaining <= 0 ? 'closed' : 'active',
        ]);
        $this->audit->log('advance_deduct', 'payroll', 'payroll_advance', $advanceId, $advance, ['amount' => $amount]);
    }

    public function close(int $id): void
    {
        $old = $this->advances->find($id);
        if (! $old) {
            throw new RuntimeException('Advance not found.');
        }

        $this->advances->update($id, ['status' => 'closed']);
        $this->audit->log('advance_close', 'payroll', 'payroll_advance', $id, $old, ['status' => 'closed']);
    }
}
