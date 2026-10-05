<?php

namespace App\Services;

use App\Models\LeavePolicyModel;
use RuntimeException;

class LeavePolicyService
{
    private LeavePolicyModel $policies;

    public function __construct(private AuditService $audit = new AuditService())
    {
        $this->policies = new LeavePolicyModel(service('tenantContext')->db());
    }

    public function create(array $data): int
    {
        $data['created_by'] = session('tenant_user_id');
        $id                 = $this->policies->insert($data);
        $this->audit->log('leave_policy_create', 'leave', 'leave_policy', $id, null, $data);

        return $id;
    }

    public function update(int $id, array $data): void
    {
        $old = $this->policies->find($id);
        if (! $old) {
            throw new RuntimeException('Leave policy not found.');
        }

        $data['updated_by'] = session('tenant_user_id');
        $this->policies->update($id, $data);
        $this->audit->log('leave_policy_update', 'leave', 'leave_policy', $id, $old, $data);
    }

    public function delete(int $id): void
    {
        $old = $this->policies->find($id);
        if (! $old) {
            throw new RuntimeException('Leave policy not found.');
        }
        if ((int) $old['is_default'] === 1) {
            throw new RuntimeException('The company-wide default policy cannot be deleted.');
        }

        $this->policies->delete($id);
        $this->audit->log('leave_policy_delete', 'leave', 'leave_policy', $id, $old, null);
    }
}
