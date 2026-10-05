<?php

namespace App\Services;

use App\Models\DepartmentModel;
use RuntimeException;

class DepartmentService
{
    private DepartmentModel $departments;

    public function __construct(
        private AuditService $audit = new AuditService(),
        private LookupCacheService $cache = new LookupCacheService()
    ) {
        $this->departments = new DepartmentModel(service('tenantContext')->db());
    }

    public function create(array $data): int
    {
        $this->assertCodeAvailable($data['code'], null);

        $id = $this->departments->insert($data);
        $this->audit->log('create', 'departments', 'department', $id, null, $data);
        $this->cache->invalidate('departments');

        return $id;
    }

    public function update(int $id, array $data): void
    {
        $this->assertCodeAvailable($data['code'], $id);

        $old = $this->departments->find($id);
        $this->departments->update($id, $data);
        $this->audit->log('update', 'departments', 'department', $id, $old, $data);
        $this->cache->invalidate('departments');
    }

    public function delete(int $id): void
    {
        // See BranchService::delete() — soft delete bypasses the
        // designations.department_id FK RESTRICT entirely.
        $designationCount = service('tenantContext')->db()->table('designations')->where('department_id', $id)->where('deleted_at', null)->countAllResults();
        if ($designationCount > 0) {
            throw new RuntimeException('This department still has designations assigned to it. Reassign or delete them first.');
        }

        $this->departments->delete($id);
        $this->audit->log('delete', 'departments', 'department', $id, null, null);
        $this->cache->invalidate('departments');
    }

    public function restore(int $id): void
    {
        $old = $this->departments->onlyDeleted()->find($id);
        if (! $old) {
            throw new RuntimeException('Archived department not found.');
        }

        $this->assertCodeAvailable($old['code'], $id);

        $this->departments->update($id, ['deleted_at' => null]);
        $this->audit->log('restore', 'departments', 'department', $id, ['deleted_at' => $old['deleted_at']], ['deleted_at' => null]);
        $this->cache->invalidate('departments');
    }

    private function assertCodeAvailable(string $code, ?int $ignoreId): void
    {
        $existing = $this->departments->where('code', $code)->first();
        if ($existing && (int) $existing['id'] !== (int) $ignoreId) {
            throw new RuntimeException('This department code is already in use.');
        }
    }
}
