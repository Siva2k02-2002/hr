<?php

namespace App\Services;

use App\Models\PayrollArrearsModel;
use App\Models\PayrollRunItemModel;
use RuntimeException;

class PayrollArrearsService
{
    private PayrollArrearsModel $arrears;

    public function __construct(private AuditService $audit = new AuditService())
    {
        $this->arrears = new PayrollArrearsModel(service('tenantContext')->db());
    }

    public function create(array $data): int
    {
        $data['created_by'] = session('tenant_user_id');
        $id                 = $this->arrears->insert($data, true);
        $this->audit->log('arrears_create', 'payroll', 'payroll_arrears', $id, null, $data);

        return $id;
    }

    public function delete(int $id): void
    {
        $old = $this->arrears->find($id);
        if (! $old) {
            throw new RuntimeException('Arrears entry not found.');
        }
        if ($old['status'] === 'paid') {
            throw new RuntimeException('Paid arrears cannot be deleted.');
        }
        if ($this->isConsumedByOpenRun($id, (int) $old['employee_id'])) {
            throw new RuntimeException('This arrears entry is already included in a generated payroll run for this employee. Cancel and regenerate that payroll run first, so its totals stay in sync — deleting it here would leave that run showing a stale arrears amount.');
        }

        $this->arrears->delete($id);
        $this->audit->log('arrears_delete', 'payroll', 'payroll_arrears', $id, $old, null);
    }

    /**
     * A draft/generated (not yet approved) run's earnings_breakdown freezes which arrears
     * rows it summed at generation time (source_ids.arrears) — approval is what marks those
     * rows 'paid' (see PayrollEarningsService); until then, this is the only record of which
     * open run(s) a given pending arrears row already fed into.
     */
    private function isConsumedByOpenRun(int $arrearsId, int $employeeId): bool
    {
        foreach ((new PayrollRunItemModel(service('tenantContext')->db()))->openForEmployee($employeeId) as $item) {
            $breakdown = json_decode((string) $item['earnings_breakdown'], true) ?: [];
            $ids       = array_map('strval', $breakdown['source_ids']['arrears'] ?? []);
            if (in_array((string) $arrearsId, $ids, true)) {
                return true;
            }
        }

        return false;
    }
}
