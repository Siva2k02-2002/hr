<?php

namespace App\Services;

use App\Models\AttendanceSettingModel;

class AttendanceSettingsService
{
    private AttendanceSettingModel $settings;

    public function __construct(private AuditService $audit = new AuditService())
    {
        $this->settings = new AttendanceSettingModel(service('tenantContext')->db());
    }

    public function current(): array
    {
        return $this->settings->current();
    }

    public function update(array $data): void
    {
        $old = $this->settings->current();
        $this->settings->update($old['id'], $data);
        $this->audit->log('settings_update', 'attendance', 'attendance_settings', $old['id'], $old, $data);
    }
}
