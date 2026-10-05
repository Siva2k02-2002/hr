<?php

namespace App\Models;

use CodeIgniter\Model;

class AttendanceWeeklyOffModel extends Model
{
    protected $table         = 'attendance_weekly_offs';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['name', 'day_of_week', 'week_pattern', 'branch_id', 'shift_id', 'status'];

    protected $validationRules = [
        'name'        => 'required|max_length[100]',
        'day_of_week' => 'required|in_list[sunday,monday,tuesday,wednesday,thursday,friday,saturday]',
    ];

    /**
     * Active rules applying to a branch/shift — global (branch_id null) rules plus
     * that branch's own, further narrowed to rules with no shift_id (apply to every
     * shift) or a shift_id matching the employee's own shift (a rotational/night
     * shift's weekly off can differ from the general shift's).
     */
    public function rulesFor(?int $branchId, ?int $shiftId = null): array
    {
        $query = $this->where('status', 'active');
        if ($branchId !== null) {
            $query->groupStart()->where('branch_id', $branchId)->orWhere('branch_id', null)->groupEnd();
        } else {
            $query->where('branch_id', null);
        }

        if ($shiftId !== null) {
            $query->groupStart()->where('shift_id', $shiftId)->orWhere('shift_id', null)->groupEnd();
        } else {
            $query->where('shift_id', null);
        }

        return $query->findAll();
    }
}
