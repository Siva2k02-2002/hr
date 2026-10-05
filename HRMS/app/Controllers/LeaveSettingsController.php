<?php

namespace App\Controllers;

use App\Services\LeaveSettingsService;

class LeaveSettingsController extends BaseController
{
    public function index()
    {
        return view('leave/settings', [
            'title'    => 'Leave Settings',
            'settings' => (new LeaveSettingsService())->current(),
        ]);
    }

    public function update()
    {
        $post = $this->request->getPost();

        (new LeaveSettingsService())->update([
            'financial_year_start_month'      => (int) ($post['financial_year_start_month'] ?? 1),
            'leave_year_start_month'          => (int) ($post['leave_year_start_month'] ?? 1),
            'half_day_enabled'                => ! empty($post['half_day_enabled']) ? 1 : 0,
            'sandwich_leave_enabled'          => ! empty($post['sandwich_leave_enabled']) ? 1 : 0,
            'holiday_between_leave_policy'    => $post['holiday_between_leave_policy'] ?? 'not_count',
            'weekly_off_between_leave_policy' => $post['weekly_off_between_leave_policy'] ?? 'not_count',
            'carry_forward_enabled'           => ! empty($post['carry_forward_enabled']) ? 1 : 0,
            'carry_forward_limit'             => (float) ($post['carry_forward_limit'] ?? 0),
            'carry_forward_expiry_month'      => empty($post['carry_forward_expiry_month']) ? null : (int) $post['carry_forward_expiry_month'],
            'leave_encashment_enabled'        => ! empty($post['leave_encashment_enabled']) ? 1 : 0,
            'max_consecutive_leave'           => empty($post['max_consecutive_leave']) ? null : (int) $post['max_consecutive_leave'],
            'min_notice_days'                 => (int) ($post['min_notice_days'] ?? 0),
            'max_future_apply_days'           => (int) ($post['max_future_apply_days'] ?? 90),
            'allow_negative_balance'          => ! empty($post['allow_negative_balance']) ? 1 : 0,
            'self_approval_allowed_for_admin' => ! empty($post['self_approval_allowed_for_admin']) ? 1 : 0,
            'half_day_hours'                  => (float) ($post['half_day_hours'] ?? 4.0),
        ]);

        return redirect()->to(site_url('leave/settings'))->with('success', 'Leave settings updated.');
    }
}
