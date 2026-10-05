<?php

namespace App\Services;

use App\Models\PayrollBonusModel;
use RuntimeException;

class PayrollBonusService
{
    private PayrollBonusModel $bonuses;

    public function __construct(private AuditService $audit = new AuditService())
    {
        $this->bonuses = new PayrollBonusModel(service('tenantContext')->db());
    }

    /** Bulk-apply to multiple employees at once — one bonus_batch_id groups the rows created. Duplicate-bonus (same employee/month/type) entries are skipped, not errored, so a partial re-submit is safe. */
    public function applyToEmployees(array $employeeIds, string $bonusType, float $amount, ?int $payrollMonthId, ?string $remarks): array
    {
        $batchId = uniqid('bonus_', true);
        $created = [];

        foreach ($employeeIds as $employeeId) {
            if ($this->bonuses->existsFor((int) $employeeId, $payrollMonthId, $bonusType)) {
                continue;
            }

            $data = [
                'bonus_batch_id'   => $batchId,
                'employee_id'      => (int) $employeeId,
                'bonus_type'       => $bonusType,
                'amount'           => $amount,
                'payroll_month_id' => $payrollMonthId,
                'remarks'          => $remarks,
                'status'           => 'pending',
                'created_by'       => session('tenant_user_id'),
            ];
            $id         = $this->bonuses->insert($data, true);
            $created[]  = $id;
            $this->audit->log('bonus_create', 'payroll', 'payroll_bonus', $id, null, $data);
        }

        return $created;
    }

    public function delete(int $id): void
    {
        $old = $this->bonuses->find($id);
        if (! $old) {
            throw new RuntimeException('Bonus entry not found.');
        }
        if ($old['status'] === 'paid') {
            throw new RuntimeException('A paid bonus cannot be deleted.');
        }

        $this->bonuses->delete($id);
        $this->audit->log('bonus_delete', 'payroll', 'payroll_bonus', $id, $old, null);
    }

    public function markPaid(int $id): void
    {
        $old = $this->bonuses->find($id);
        if (! $old) {
            throw new RuntimeException('Bonus entry not found.');
        }

        $this->bonuses->update($id, ['status' => 'paid']);
        $this->audit->log('bonus_paid', 'payroll', 'payroll_bonus', $id, $old, ['status' => 'paid']);
    }
}
