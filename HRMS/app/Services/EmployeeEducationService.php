<?php

namespace App\Services;

use App\Models\EmployeeEducationModel;
use RuntimeException;

class EmployeeEducationService
{
    private EmployeeEducationModel $records;

    public function __construct(private AuditService $audit = new AuditService())
    {
        $this->records = new EmployeeEducationModel(service('tenantContext')->db());
    }

    public function create(array $data): int
    {
        $id = $this->records->insert($data);
        $this->audit->log('create', 'employees', 'employee_education', $id, null, $data);

        return $id;
    }

    public function update(int $id, array $data): void
    {
        $old = $this->records->find($id);
        if (! $old) {
            throw new RuntimeException('Education record not found.');
        }

        $this->records->update($id, $data);
        $this->audit->log('update', 'employees', 'employee_education', $id, $old, $data);
    }

    public function delete(int $id): void
    {
        $old = $this->records->find($id);
        if (! $old) {
            throw new RuntimeException('Education record not found.');
        }

        $this->records->delete($id);
        $this->audit->log('delete', 'employees', 'employee_education', $id, $old, null);
    }
}
