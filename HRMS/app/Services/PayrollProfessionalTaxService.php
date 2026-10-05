<?php

namespace App\Services;

use App\Models\PayrollProfessionalTaxSettingModel;
use RuntimeException;

class PayrollProfessionalTaxService
{
    private PayrollProfessionalTaxSettingModel $slabs;

    public function __construct(private AuditService $audit = new AuditService())
    {
        $this->slabs = new PayrollProfessionalTaxSettingModel(service('tenantContext')->db());
    }

    public function allSlabs(): array
    {
        return $this->slabs->orderBy('state')->orderBy('min_gross')->findAll();
    }

    public function statesList(): array
    {
        return $this->slabs->statesList();
    }

    public function create(array $data): int
    {
        $id = $this->slabs->insert($data, true);
        $this->audit->log('payroll_pt_slab_create', 'payroll', 'payroll_professional_tax_setting', $id, null, $data);

        return $id;
    }

    public function update(int $id, array $data): void
    {
        $old = $this->slabs->find($id);
        if (! $old) {
            throw new RuntimeException('Professional tax slab not found.');
        }
        $this->slabs->update($id, $data);
        $this->audit->log('payroll_pt_slab_update', 'payroll', 'payroll_professional_tax_setting', $id, $old, $data);
    }

    public function delete(int $id): void
    {
        $old = $this->slabs->find($id);
        if (! $old) {
            throw new RuntimeException('Professional tax slab not found.');
        }
        $this->slabs->delete($id);
        $this->audit->log('payroll_pt_slab_delete', 'payroll', 'payroll_professional_tax_setting', $id, $old, null);
    }

    /** 0 if no slab matches (e.g. state not configured) — professional tax is state-specific and silently absent states shouldn't block a payroll run. */
    public function calculate(?string $state, float $grossAmount): float
    {
        if (! $state) {
            return 0.0;
        }

        $slab = $this->slabs->slabFor($state, $grossAmount);

        return $slab ? (float) $slab['tax_amount'] : 0.0;
    }
}
