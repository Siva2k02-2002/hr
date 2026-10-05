<?php

namespace App\Services;

use App\Models\DesignationModel;
use RuntimeException;

class DesignationService
{
    private DesignationModel $designations;

    public function __construct(
        private AuditService $audit = new AuditService(),
        private LookupCacheService $cache = new LookupCacheService()
    ) {
        $this->designations = new DesignationModel(service('tenantContext')->db());
    }

    public function create(array $data): int
    {
        $this->assertNameAvailable($data['department_id'], $data['name'], null);

        $id = $this->designations->insert($data);
        $this->audit->log('create', 'designations', 'designation', $id, null, $data);
        $this->cache->invalidate('designations');

        return $id;
    }

    public function update(int $id, array $data): void
    {
        $this->assertNameAvailable($data['department_id'], $data['name'], $id);

        $old = $this->designations->find($id);
        $this->designations->update($id, $data);
        $this->audit->log('update', 'designations', 'designation', $id, $old, $data);
        $this->cache->invalidate('designations');
    }

    public function delete(int $id): void
    {
        // Soft delete bypasses employees.designation_id's FK RESTRICT entirely (the row
        // is never physically removed) — see BranchService/DepartmentService::delete()
        // for the same pattern one level up the hierarchy.
        $employeeCount = service('tenantContext')->db()->table('employees')->where('designation_id', $id)->where('deleted_at', null)->countAllResults();
        if ($employeeCount > 0) {
            throw new RuntimeException('This designation still has employees assigned to it. Reassign them first.');
        }

        $this->designations->delete($id);
        $this->audit->log('delete', 'designations', 'designation', $id, null, null);
        $this->cache->invalidate('designations');
    }

    public function restore(int $id): void
    {
        $old = $this->designations->onlyDeleted()->find($id);
        if (! $old) {
            throw new RuntimeException('Archived designation not found.');
        }

        $this->assertNameAvailable($old['department_id'], $old['name'], $id);

        $this->designations->update($id, ['deleted_at' => null]);
        $this->audit->log('restore', 'designations', 'designation', $id, ['deleted_at' => $old['deleted_at']], ['deleted_at' => null]);
        $this->cache->invalidate('designations');
    }

    /** Two active designations with the same name in the same department is almost always a data-entry duplicate (audit finding EMP-03). */
    private function assertNameAvailable(int $departmentId, string $name, ?int $ignoreId): void
    {
        $query = $this->designations->where('department_id', $departmentId)->where('name', $name);
        if ($ignoreId !== null) {
            $query->where('id !=', $ignoreId);
        }

        if ($query->countAllResults() > 0) {
            throw new RuntimeException('A designation with this name already exists in this department.');
        }
    }
}
