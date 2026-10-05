<?php

namespace App\Services;

use App\Models\PayrollEsiSettingModel;

class PayrollEsiService
{
    private PayrollEsiSettingModel $settings;

    public function __construct(private AuditService $audit = new AuditService())
    {
        $this->settings = new PayrollEsiSettingModel(service('tenantContext')->db());
    }

    public function current(): array
    {
        return $this->settings->current();
    }

    public function update(array $data): void
    {
        $old = $this->current();
        $this->settings->update($old['id'], $data);
        $this->audit->log('payroll_esi_settings_update', 'payroll', 'payroll_esi_setting', (int) $old['id'], $old, $data);
    }

    /** ESI is all-or-nothing eligibility — over the wage ceiling, the employee isn't covered at all (both shares are zero), not capped like PF. */
    public function calculate(float $esiWage): array
    {
        $settings = $this->current();
        $ceiling  = (float) $settings['wage_ceiling'];

        if ($ceiling > 0 && $esiWage > $ceiling) {
            return ['eligible' => false, 'employee' => 0.0, 'employer' => 0.0];
        }

        return [
            'eligible' => true,
            'employee' => round($esiWage * (float) $settings['employee_percentage'] / 100, 2),
            'employer' => round($esiWage * (float) $settings['employer_percentage'] / 100, 2),
        ];
    }
}
