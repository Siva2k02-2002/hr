<?php

namespace App\Models;

use CodeIgniter\Model;

/** The daily aggregate — recomputed from attendance_logs by AttendanceSummaryService, never written to directly by the punch flow. */
class AttendanceModel extends Model
{
    protected $table          = 'attendance';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $useTimestamps  = true;
    protected $useSoftDeletes = true;
    protected $deletedField   = 'deleted_at';
    protected $allowedFields  = [
        'employee_id', 'attendance_date', 'shift_id', 'first_punch_in_at', 'last_punch_out_at',
        'working_minutes', 'break_minutes', 'late_minutes', 'early_exit_minutes', 'overtime_minutes',
        'status', 'source', 'created_by', 'updated_by',
    ];

    protected $validationRules = [
        'employee_id'     => 'required|integer',
        'attendance_date' => 'required|valid_date',
        'status'          => 'required|in_list[present,absent,half_day,holiday,weekly_off,leave,on_duty,work_from_home,late,missed_punch,half_day_leave,lop]',
    ];

    public function forEmployeeAndDate(int $employeeId, string $date): ?array
    {
        return $this->where('employee_id', $employeeId)->where('attendance_date', $date)->first();
    }

    public function withRelations()
    {
        return $this->select("
                attendance.*,
                CONCAT(e.first_name, ' ', e.last_name) as employee_name, e.employee_code, e.branch_id, e.department_id,
                s.name as shift_name
            ")
            ->join('employees e', 'e.id = attendance.employee_id')
            ->join('attendance_shifts s', 's.id = attendance.shift_id', 'left');
    }

    public function monthFor(int $employeeId, int $year, int $month): array
    {
        return $this->where('employee_id', $employeeId)
            ->where('YEAR(attendance_date)', $year)
            ->where('MONTH(attendance_date)', $month)
            ->orderBy('attendance_date')
            ->findAll();
    }
}
