<?php

namespace App\Models;

use CodeIgniter\Model;

/** Single-row table — always operate on the first (and only) row. Same pattern as AttendanceSettingModel. */
class LeaveSettingModel extends Model
{
    protected $table         = 'leave_settings';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'financial_year_start_month', 'leave_year_start_month', 'half_day_enabled', 'sandwich_leave_enabled',
        'holiday_between_leave_policy', 'weekly_off_between_leave_policy', 'carry_forward_enabled',
        'carry_forward_limit', 'carry_forward_expiry_month', 'leave_encashment_enabled', 'max_consecutive_leave',
        'min_notice_days', 'max_future_apply_days', 'allow_negative_balance', 'self_approval_allowed_for_admin',
        'half_day_hours',
    ];

    /** Lazily creates the single default row the first time it's needed — same idiom as AttendanceSettingModel::current(). */
    public function current(): array
    {
        $row = $this->orderBy('id', 'asc')->first();
        if ($row) {
            return $row;
        }

        $id = $this->insert([
            'financial_year_start_month' => 1, 'leave_year_start_month' => 1, 'half_day_enabled' => 1,
            'sandwich_leave_enabled' => 0, 'holiday_between_leave_policy' => 'not_count',
            'weekly_off_between_leave_policy' => 'not_count', 'carry_forward_enabled' => 1, 'carry_forward_limit' => 0,
            'leave_encashment_enabled' => 0, 'min_notice_days' => 0, 'max_future_apply_days' => 90,
            'allow_negative_balance' => 0, 'self_approval_allowed_for_admin' => 1, 'half_day_hours' => 4.0,
        ], true);

        return $this->find($id);
    }
}
