<?php

namespace App\Models;

use CodeIgniter\Model;

/** Single-row table — always operate on the first (and only) row. Same pattern as CompanySettingModel. */
class AttendanceSettingModel extends Model
{
    protected $table         = 'attendance_settings';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'default_shift_id', 'grace_minutes', 'late_mark_minutes', 'half_day_minutes', 'full_day_minutes',
        'gps_required', 'device_approval_required', 'self_attendance_enabled', 'overtime_enabled',
        'weekend_policy', 'holiday_policy', 'timezone',
    ];

    /**
     * Lazily creates the single default row the first time it's needed — no
     * provisioning-step change required. Model::insert() rejects a truly
     * empty array (it can't build an INSERT with no fields), so the row's
     * defaults are spelled out here even though the migration also sets
     * them at the DB level.
     */
    public function current(): array
    {
        $row = $this->orderBy('id', 'asc')->first();
        if ($row) {
            return $row;
        }

        $id = $this->insert([
            'grace_minutes' => 10, 'late_mark_minutes' => 15, 'half_day_minutes' => 240, 'full_day_minutes' => 480,
            'gps_required' => 1, 'device_approval_required' => 0, 'self_attendance_enabled' => 1, 'overtime_enabled' => 1,
            'weekend_policy' => 'Sunday Off', 'holiday_policy' => 'Paid', 'timezone' => 'Asia/Kolkata',
        ], true);

        return $this->find($id);
    }
}
