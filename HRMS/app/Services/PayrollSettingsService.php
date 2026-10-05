<?php

namespace App\Services;

use App\Models\PayrollSettingModel;

class PayrollSettingsService
{
    private PayrollSettingModel $settings;

    public function __construct(private AuditService $audit = new AuditService())
    {
        $this->settings = new PayrollSettingModel(service('tenantContext')->db());
    }

    public function current(): array
    {
        return $this->settings->current();
    }

    public function update(array $data): void
    {
        $old = $this->current();
        $this->settings->update($old['id'], $data);
        $this->audit->log('payroll_settings_update', 'payroll', 'payroll_setting', (int) $old['id'], $old, $data);
    }
}
