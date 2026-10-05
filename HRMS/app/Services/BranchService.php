<?php

namespace App\Services;

use App\Models\BranchModel;
use RuntimeException;

class BranchService
{
    private BranchModel $branches;

    public function __construct(
        private AuditService $audit = new AuditService(),
        private LookupCacheService $cache = new LookupCacheService()
    ) {
        $this->branches = new BranchModel(service('tenantContext')->db());
    }

    public function create(array $data): int
    {
        $this->assertCodeAvailable($data['code'], null);

        $id = $this->branches->insert($data);
        $this->audit->log('create', 'branches', 'branch', $id, null, $data);
        $this->cache->invalidate('branches');

        return $id;
    }

    public function update(int $id, array $data): void
    {
        $this->assertCodeAvailable($data['code'], $id);

        $old = $this->branches->find($id);
        $this->branches->update($id, $data);
        $this->audit->log('update', 'branches', 'branch', $id, $old, $data);
        $this->cache->invalidate('branches');
    }

    public function delete(int $id): void
    {
        // Soft delete bypasses the departments.branch_id FK RESTRICT entirely
        // (the row is never physically removed), so a branch with live
        // departments would otherwise vanish from lists while its
        // departments kept silently pointing at it. Checked explicitly here.
        $departmentCount = service('tenantContext')->db()->table('departments')->where('branch_id', $id)->where('deleted_at', null)->countAllResults();
        if ($departmentCount > 0) {
            throw new RuntimeException('This branch still has departments assigned to it. Reassign or delete them first.');
        }

        $this->branches->delete($id);
        $this->audit->log('delete', 'branches', 'branch', $id, null, null);
        $this->cache->invalidate('branches');
    }

    public function restore(int $id): void
    {
        $old = $this->branches->onlyDeleted()->find($id);
        if (! $old) {
            throw new RuntimeException('Archived branch not found.');
        }

        $this->assertCodeAvailable($old['code'], $id);

        $this->branches->update($id, ['deleted_at' => null]);
        $this->audit->log('restore', 'branches', 'branch', $id, ['deleted_at' => $old['deleted_at']], ['deleted_at' => null]);
        $this->cache->invalidate('branches');
    }

    /** BranchModel can't use is_unique against a dynamic tenant connection — see UserModel's note. */
    private function assertCodeAvailable(string $code, ?int $ignoreId): void
    {
        $existing = $this->branches->where('code', $code)->first();
        if ($existing && (int) $existing['id'] !== (int) $ignoreId) {
            throw new RuntimeException('This branch code is already in use.');
        }
    }
}
