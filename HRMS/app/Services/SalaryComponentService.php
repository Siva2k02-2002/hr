<?php

namespace App\Services;

use App\Models\PayrollSalaryComponentModel;
use RuntimeException;

class SalaryComponentService
{
    private PayrollSalaryComponentModel $components;

    public function __construct(private AuditService $audit = new AuditService())
    {
        $this->components = new PayrollSalaryComponentModel(service('tenantContext')->db());
    }

    public function create(array $data): int
    {
        $this->assertUniqueCode($data['code'], null);

        $data['created_by'] = session('tenant_user_id');
        $id                 = $this->components->insert($data, true);
        $this->audit->log('salary_component_create', 'payroll', 'payroll_salary_component', $id, null, $data);

        return $id;
    }

    public function update(int $id, array $data): void
    {
        $old = $this->components->find($id);
        if (! $old) {
            throw new RuntimeException('Salary component not found.');
        }
        $this->assertUniqueCode($data['code'], $id);

        $data['updated_by'] = session('tenant_user_id');
        $this->components->update($id, $data);
        $this->audit->log('salary_component_update', 'payroll', 'payroll_salary_component', $id, $old, $data);
    }

    public function delete(int $id): void
    {
        $old = $this->components->find($id);
        if (! $old) {
            throw new RuntimeException('Salary component not found.');
        }

        $inUse = service('tenantContext')->db()->table('payroll_salary_structure_items')->where('salary_component_id', $id)->countAllResults();
        if ($inUse > 0) {
            throw new RuntimeException('This component is used by one or more salary structures and cannot be deleted.');
        }

        $this->components->delete($id);
        $this->audit->log('salary_component_delete', 'payroll', 'payroll_salary_component', $id, $old, null);
    }

    private function assertUniqueCode(string $code, ?int $ignoreId): void
    {
        $query = service('tenantContext')->db()->table('payroll_salary_components')->where('code', $code)->where('deleted_at', null);
        if ($ignoreId !== null) {
            $query->where('id !=', $ignoreId);
        }
        if ($query->countAllResults() > 0) {
            throw new RuntimeException('A salary component with this code already exists.');
        }
    }
}
