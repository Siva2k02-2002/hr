<?php

namespace App\Services;

use App\Models\PayrollIncentiveModel;
use RuntimeException;

class PayrollIncentiveService
{
    private PayrollIncentiveModel $incentives;

    public function __construct(private AuditService $audit = new AuditService())
    {
        $this->incentives = new PayrollIncentiveModel(service('tenantContext')->db());
    }

    public function create(array $data): int
    {
        $data['created_by'] = session('tenant_user_id');
        $id                 = $this->incentives->insert($data, true);
        $this->audit->log('incentive_create', 'payroll', 'payroll_incentive', $id, null, $data);

        return $id;
    }

    public function delete(int $id): void
    {
        $old = $this->incentives->find($id);
        if (! $old) {
            throw new RuntimeException('Incentive entry not found.');
        }
        if ($old['status'] === 'paid') {
            throw new RuntimeException('A paid incentive cannot be deleted.');
        }

        $this->incentives->delete($id);
        $this->audit->log('incentive_delete', 'payroll', 'payroll_incentive', $id, $old, null);
    }
}
