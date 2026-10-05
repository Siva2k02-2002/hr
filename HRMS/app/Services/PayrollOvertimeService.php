<?php

namespace App\Services;

use App\Models\AttendanceSettingModel;

/** Hourly rate = basis amount / (working days * standard hours/day). Standard hours/day is read from attendance_settings.full_day_minutes rather than duplicating a second "hours per day" config. */
class PayrollOvertimeService
{
    public function calculate(float $basisAmount, float $workingDays, float $overtimeHours, float $multiplier): float
    {
        if ($workingDays <= 0 || $overtimeHours <= 0) {
            return 0.0;
        }

        $standardHoursPerDay = max(1, (int) (new AttendanceSettingModel(service('tenantContext')->db()))->current()['full_day_minutes']) / 60;
        $hourlyRate          = $basisAmount / ($workingDays * $standardHoursPerDay);

        return round($hourlyRate * $overtimeHours * $multiplier, 2);
    }
}
