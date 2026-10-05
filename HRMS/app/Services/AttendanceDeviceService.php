<?php

namespace App\Services;

use App\Models\AttendanceDeviceModel;
use App\Models\AttendanceSettingModel;

/**
 * Auto-registers a never-seen device as 'pending' (or 'approved' if the
 * company doesn't require approval) the first time it's used to punch, or
 * just touches last_login_at on a returning one. Never blocks the punch
 * itself — approval is a retroactive HR review flag (see the plan).
 */
class AttendanceDeviceService
{
    private AttendanceDeviceModel $devices;

    public function __construct(private AuditService $audit = new AuditService())
    {
        $this->devices = new AttendanceDeviceModel(service('tenantContext')->db());
    }

    public function registerOrTouch(int $employeeId, string $deviceUid, ?string $deviceName): array
    {
        $existing = $this->devices->findByUid($employeeId, $deviceUid);
        $request  = service('request');
        $now      = date('Y-m-d H:i:s');

        if ($existing) {
            $this->devices->update($existing['id'], ['last_login_at' => $now, 'ip_address' => $request->getIPAddress()]);

            return $this->devices->find($existing['id']);
        }

        $settings  = (new AttendanceSettingModel(service('tenantContext')->db()))->current();
        $userAgent = (string) $request->getUserAgent();

        $id = $this->devices->insert([
            'employee_id'    => $employeeId,
            'device_uid'     => $deviceUid,
            'device_name'    => $deviceName,
            'browser'        => $this->parseBrowser($userAgent),
            'os'             => $this->parseOs($userAgent),
            'user_agent'     => $userAgent,
            'ip_address'     => $request->getIPAddress(),
            'first_login_at' => $now,
            'last_login_at'  => $now,
            'status'         => empty($settings['device_approval_required']) ? 'approved' : 'pending',
        ], true);

        $this->audit->log('device_register', 'attendance', 'attendance_device', $id, null, [
            'employee_id' => $employeeId, 'device_uid' => $deviceUid,
        ]);

        return $this->devices->find($id);
    }

    public function approve(int $id, int $approvedBy): void
    {
        $this->transition($id, 'approved', $approvedBy);
    }

    public function reject(int $id, int $approvedBy): void
    {
        $this->transition($id, 'rejected', $approvedBy);
    }

    public function block(int $id, int $approvedBy): void
    {
        $this->transition($id, 'blocked', $approvedBy);
    }

    private function transition(int $id, string $status, int $approvedBy): void
    {
        $old = $this->devices->find($id);
        $this->devices->update($id, ['status' => $status, 'approved_by' => $approvedBy, 'approved_at' => date('Y-m-d H:i:s')]);
        $this->audit->log('device_' . $status, 'attendance', 'attendance_device', $id, ['status' => $old['status']], ['status' => $status]);
    }

    private function parseBrowser(string $ua): string
    {
        return match (true) {
            str_contains($ua, 'Edg/')     => 'Edge',
            str_contains($ua, 'Chrome/')  => 'Chrome',
            str_contains($ua, 'Firefox/') => 'Firefox',
            str_contains($ua, 'Safari/') && ! str_contains($ua, 'Chrome') => 'Safari',
            default => 'Unknown',
        };
    }

    private function parseOs(string $ua): string
    {
        return match (true) {
            str_contains($ua, 'Windows') => 'Windows',
            str_contains($ua, 'Mac OS')  => 'macOS',
            str_contains($ua, 'Android') => 'Android',
            str_contains($ua, 'iPhone') || str_contains($ua, 'iPad') => 'iOS',
            str_contains($ua, 'Linux')   => 'Linux',
            default => 'Unknown',
        };
    }
}
