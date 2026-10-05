<?php

namespace App\Services;

use App\Models\EmployeeExperienceModel;
use RuntimeException;

class EmployeeExperienceService
{
    private EmployeeExperienceModel $records;

    public function __construct(private AuditService $audit = new AuditService())
    {
        $this->records = new EmployeeExperienceModel(service('tenantContext')->db());
    }

    public function create(array $data): int
    {
        $id = $this->records->insert($data);
        $this->audit->log('create', 'employees', 'employee_experience', $id, null, $data);

        return $id;
    }

    public function update(int $id, array $data): void
    {
        $old = $this->records->find($id);
        if (! $old) {
            throw new RuntimeException('Experience record not found.');
        }

        $this->records->update($id, $data);
        $this->audit->log('update', 'employees', 'employee_experience', $id, $old, $data);
    }

    public function delete(int $id): void
    {
        $old = $this->records->find($id);
        if (! $old) {
            throw new RuntimeException('Experience record not found.');
        }

        $this->records->delete($id);
        $this->audit->log('delete', 'employees', 'employee_experience', $id, $old, null);
    }
}
