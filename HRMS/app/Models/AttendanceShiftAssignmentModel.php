<?php

namespace App\Models;

use CodeIgniter\Model;

class AttendanceShiftAssignmentModel extends Model
{
    protected $table         = 'attendance_shift_assignments';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = ['employee_id', 'shift_id', 'effective_from', 'effective_to', 'created_by', 'created_at'];

    /** The assignment in force for $employeeId on $date, or null if none was ever set (caller falls back to the default shift). */
    public function currentFor(int $employeeId, string $date): ?array
    {
        return $this->select('attendance_shift_assignments.*, s.name as shift_name, s.code as shift_code, s.start_time, s.end_time, s.grace_minutes, s.full_day_minutes, s.is_night_shift')
            ->join('attendance_shifts s', 's.id = attendance_shift_assignments.shift_id')
            ->where('attendance_shift_assignments.employee_id', $employeeId)
            ->where('effective_from <=', $date)
            ->groupStart()
                ->where('effective_to >=', $date)
                ->orWhere('effective_to', null)
            ->groupEnd()
            ->orderBy('effective_from', 'DESC')
            ->first();
    }

    public function historyFor(int $employeeId): array
    {
        return $this->select('attendance_shift_assignments.*, s.name as shift_name, s.code as shift_code, u.name as changed_by_name')
            ->join('attendance_shifts s', 's.id = attendance_shift_assignments.shift_id')
            ->join('users u', 'u.id = attendance_shift_assignments.created_by', 'left')
            ->where('attendance_shift_assignments.employee_id', $employeeId)
            ->orderBy('effective_from', 'DESC')
            ->findAll();
    }
}
