<?php

namespace App\Services;

use App\Models\LeaveTypeModel;
use RuntimeException;

class LeaveTypeService
{
    private LeaveTypeModel $types;

    public function __construct(
        private AuditService $audit = new AuditService(),
        private LookupCacheService $cache = new LookupCacheService()
    ) {
        $this->types = new LeaveTypeModel(service('tenantContext')->db());
    }

    public function create(array $data): int
    {
        $this->assertUniqueCode($data['code'], null);

        $data['created_by'] = session('tenant_user_id');
        $id                 = $this->types->insert($data);
        $this->audit->log('leave_type_create', 'leave', 'leave_type', $id, null, $data);
        $this->cache->invalidate('leave_types');

        return $id;
    }

    public function update(int $id, array $data): void
    {
        $old = $this->types->find($id);
        if (! $old) {
            throw new RuntimeException('Leave type not found.');
        }
        $this->assertUniqueCode($data['code'], $id);

        $data['updated_by'] = session('tenant_user_id');
        $this->types->update($id, $data);
        $this->audit->log('leave_type_update', 'leave', 'leave_type', $id, $old, $data);
        $this->cache->invalidate('leave_types');
    }

    public function delete(int $id): void
    {
        $old = $this->types->find($id);
        if (! $old) {
            throw new RuntimeException('Leave type not found.');
        }

        $this->types->delete($id);
        $this->audit->log('leave_type_delete', 'leave', 'leave_type', $id, $old, null);
        $this->cache->invalidate('leave_types');
    }

    private function assertUniqueCode(string $code, ?int $ignoreId): void
    {
        $query = service('tenantContext')->db()->table('leave_types')->where('code', $code)->where('deleted_at', null);
        if ($ignoreId !== null) {
            $query->where('id !=', $ignoreId);
        }
        if ($query->countAllResults() > 0) {
            throw new RuntimeException('A leave type with this code already exists.');
        }
    }
}
