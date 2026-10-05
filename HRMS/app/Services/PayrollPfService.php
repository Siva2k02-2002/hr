<?php

namespace App\Services;

use App\Models\PayrollPfSettingModel;

class PayrollPfService
{
    private PayrollPfSettingModel $settings;

    public function __construct(private AuditService $audit = new AuditService())
    {
        $this->settings = new PayrollPfSettingModel(service('tenantContext')->db());
    }

    public function current(): array
    {
        return $this->settings->current();
    }

    public function update(array $data): void
    {
        $old = $this->current();
        $this->settings->update($old['id'], $data);
        $this->audit->log('payroll_pf_settings_update', 'payroll', 'payroll_pf_setting', (int) $old['id'], $old, $data);
    }

    /** @return array{employee: float, employer: float, wage: float} */
    public function calculate(float $pfWage): array
    {
        $settings = $this->current();
        $ceiling  = (float) $settings['wage_ceiling'];
        $wage     = $ceiling > 0 ? min($pfWage, $ceiling) : $pfWage;

        return [
            'wage'     => round($wage, 2),
            'employee' => round($wage * (float) $settings['employee_percentage'] / 100, 2),
            'employer' => round($wage * (float) $settings['employer_percentage'] / 100, 2),
        ];
    }

    /** Picks the PF wage amount out of an earnings breakdown per pf_wage_basis (basic / basic_da / gross). */
    public function pfWageFromEarnings(float $basic, float $da, float $gross): float
    {
        return match ($this->current()['pf_wage_basis']) {
            'basic_da' => $basic + $da,
            'gross'    => $gross,
            default    => $basic,
        };
    }
}
