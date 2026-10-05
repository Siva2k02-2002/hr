<?php

namespace App\Services;

use App\Models\EmployeeEmergencyContactModel;
use RuntimeException;

class EmployeeEmergencyContactService
{
    private EmployeeEmergencyContactModel $contacts;

    public function __construct(private AuditService $audit = new AuditService())
    {
        $this->contacts = new EmployeeEmergencyContactModel(service('tenantContext')->db());
    }

    public function create(array $data): int
    {
        $id = $this->contacts->insert($data);
        $this->audit->log('create', 'employees', 'employee_emergency_contact', $id, null, $data);

        return $id;
    }

    public function update(int $id, array $data): void
    {
        $old = $this->contacts->find($id);
        if (! $old) {
            throw new RuntimeException('Emergency contact not found.');
        }

        $this->contacts->update($id, $data);
        $this->audit->log('update', 'employees', 'employee_emergency_contact', $id, $old, $data);
    }

    public function delete(int $id): void
    {
        $old = $this->contacts->find($id);
        if (! $old) {
            throw new RuntimeException('Emergency contact not found.');
        }

        $this->contacts->delete($id);
        $this->audit->log('delete', 'employees', 'employee_emergency_contact', $id, $old, null);
    }
}
