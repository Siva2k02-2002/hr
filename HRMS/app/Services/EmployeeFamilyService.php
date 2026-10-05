<?php

namespace App\Services;

use App\Models\EmployeeFamilyMemberModel;
use RuntimeException;

class EmployeeFamilyService
{
    private EmployeeFamilyMemberModel $members;

    public function __construct(private AuditService $audit = new AuditService())
    {
        $this->members = new EmployeeFamilyMemberModel(service('tenantContext')->db());
    }

    public function create(array $data): int
    {
        $id = $this->members->insert($data);
        $this->audit->log('create', 'employees', 'employee_family_member', $id, null, $data);

        return $id;
    }

    public function update(int $id, array $data): void
    {
        $old = $this->members->find($id);
        if (! $old) {
            throw new RuntimeException('Family member not found.');
        }

        $this->members->update($id, $data);
        $this->audit->log('update', 'employees', 'employee_family_member', $id, $old, $data);
    }

    public function delete(int $id): void
    {
        $old = $this->members->find($id);
        if (! $old) {
            throw new RuntimeException('Family member not found.');
        }

        $this->members->delete($id);
        $this->audit->log('delete', 'employees', 'employee_family_member', $id, $old, null);
    }
}
