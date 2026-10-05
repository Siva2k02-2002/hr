<?php

namespace App\Models;

use CodeIgniter\Model;

/** Foundation only — no payroll math here. Populated by AttendanceSummaryService. */
class AttendanceOvertimeModel extends Model
{
    protected $table         = 'attendance_overtime';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['employee_id', 'attendance_date', 'shift_minutes', 'worked_minutes', 'overtime_minutes', 'status'];

    public function forEmployeeAndDate(int $employeeId, string $date): ?array
    {
        return $this->where('employee_id', $employeeId)->where('attendance_date', $date)->first();
    }
}
