<?php

namespace App\Services;

use App\Models\EmployeeAddressModel;

/**
 * Addresses are always exactly one "permanent" + one "current" row per
 * employee (see the unique(employee_id, address_type) constraint) — so
 * this is an upsert by type, not open-ended CRUD like the other tabs.
 */
class EmployeeAddressService
{
    private EmployeeAddressModel $addresses;

    public function __construct(private AuditService $audit = new AuditService())
    {
        $this->addresses = new EmployeeAddressModel(service('tenantContext')->db());
    }

    public function save(int $employeeId, string $addressType, array $data): void
    {
        $data['employee_id']  = $employeeId;
        $data['address_type'] = $addressType;

        $existing = $this->addresses->where('employee_id', $employeeId)->where('address_type', $addressType)->first();

        if ($existing) {
            $this->addresses->update($existing['id'], $data);
            $this->audit->log('update', 'employees', 'employee_address', $existing['id'], $existing, $data);
        } else {
            $id = $this->addresses->insert($data);
            $this->audit->log('create', 'employees', 'employee_address', $id, null, $data);
        }
    }
}
