<?php

namespace App\Controllers;

use App\Models\AttendanceShiftModel;
use App\Services\AttendanceSettingsService;

class AttendanceSettingsController extends BaseController
{
    public function index()
    {
        $service = new AttendanceSettingsService();

        return view('attendance/settings', [
            'title'    => 'Attendance Settings',
            'settings' => $service->current(),
            'shifts'   => (new AttendanceShiftModel(service('tenantContext')->db()))->where('status', 'active')->findAll(),
        ]);
    }

    public function update()
    {
        $post = $this->request->getPost();

        (new AttendanceSettingsService())->update([
            'default_shift_id'         => empty($post['default_shift_id']) ? null : $post['default_shift_id'],
            'grace_minutes'            => (int) ($post['grace_minutes'] ?? 10),
            'late_mark_minutes'        => (int) ($post['late_mark_minutes'] ?? 15),
            'half_day_minutes'         => (int) ($post['half_day_minutes'] ?? 240),
            'full_day_minutes'         => (int) ($post['full_day_minutes'] ?? 480),
            'gps_required'             => ! empty($post['gps_required']) ? 1 : 0,
            'device_approval_required' => ! empty($post['device_approval_required']) ? 1 : 0,
            'self_attendance_enabled'  => ! empty($post['self_attendance_enabled']) ? 1 : 0,
            'overtime_enabled'         => ! empty($post['overtime_enabled']) ? 1 : 0,
            'weekend_policy'           => $post['weekend_policy'] ?? 'Sunday Off',
            'holiday_policy'           => $post['holiday_policy'] ?? 'Paid',
            'timezone'                 => $post['timezone'] ?? 'Asia/Kolkata',
        ]);

        return redirect()->to(site_url('attendance/settings'))->with('success', 'Attendance settings updated.');
    }
}
