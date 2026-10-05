<?php

namespace App\Models;

use CodeIgniter\Model;

class AttendanceShiftModel extends Model
{
    protected $table          = 'attendance_shifts';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $useTimestamps  = true;
    protected $useSoftDeletes = true;
    protected $deletedField   = 'deleted_at';
    protected $allowedFields  = [
        'name', 'code', 'start_time', 'end_time', 'break_start', 'break_end',
        'grace_minutes', 'late_minutes', 'half_day_minutes', 'full_day_minutes', 'is_night_shift', 'status',
    ];

    /** No is_unique — see DepartmentModel's note; checked by hand in AttendanceShiftService. */
    protected $validationRules = [
        'name'       => 'required|min_length[2]|max_length[100]',
        'code'       => 'required|alpha_numeric_punct|max_length[20]',
        'start_time' => 'required',
        'end_time'   => 'required',
    ];

    /** Shift length in minutes, correctly handling a night shift whose end_time crosses midnight. */
    public function durationMinutes(array $shift): int
    {
        $start = strtotime($shift['start_time']);
        $end   = strtotime($shift['end_time']);
        if ($end <= $start) {
            $end = strtotime('+1 day', $end);
        }

        return (int) round(($end - $start) / 60);
    }
}
