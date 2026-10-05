<?php

namespace App\Services;

use App\Models\PayrollTdsSettingModel;

/**
 * Foundation only — flat placeholder rate above a threshold. Real TDS needs
 * an income tax declaration module (explicitly out of scope for this phase);
 * HR overrides the computed amount per employee per run via payroll_adjustments
 * when the flat estimate isn't right for a given employee.
 */
class PayrollTdsService
{
    private PayrollTdsSettingModel $settings;

    public function __construct(private AuditService $audit = new AuditService())
    {
        $this->settings = new PayrollTdsSettingModel(service('tenantContext')->db());
    }

    public function current(): array
    {
        return $this->settings->current();
    }

    public function update(array $data): void
    {
        $old = $this->current();
        $this->settings->update($old['id'], $data);
        $this->audit->log('payroll_tds_settings_update', 'payroll', 'payroll_tds_setting', (int) $old['id'], $old, $data);
    }

    public function calculate(float $taxableGross): float
    {
        $settings = $this->current();
        if ($taxableGross <= (float) $settings['applicable_above_gross']) {
            return 0.0;
        }

        return round($taxableGross * (float) $settings['default_percentage'] / 100, 2);
    }
}
