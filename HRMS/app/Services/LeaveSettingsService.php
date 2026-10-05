<?php

namespace App\Services;

use App\Models\LeaveSettingModel;

class LeaveSettingsService
{
    public function __construct(private AuditService $audit = new AuditService())
    {
    }

    public function current(): array
    {
        return (new LeaveSettingModel(service('tenantContext')->db()))->current();
    }

    public function update(array $data): void
    {
        $model = new LeaveSettingModel(service('tenantContext')->db());
        $old   = $model->current();
        $model->update($old['id'], $data);
        $this->audit->log('leave_settings_update', 'leave', 'leave_setting', (int) $old['id'], $old, $data);
    }
}
