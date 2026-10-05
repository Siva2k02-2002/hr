<?php

namespace App\Models;

use CodeIgniter\Model;

class AttendanceHolidayModel extends Model
{
    protected $table          = 'attendance_holidays';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $useTimestamps  = true;
    protected $useSoftDeletes = true;
    protected $deletedField   = 'deleted_at';
    protected $allowedFields  = ['name', 'date', 'holiday_type', 'branch_id', 'description', 'is_optional', 'status', 'is_annual'];

    protected $validationRules = [
        'name' => 'required|max_length[150]',
        'date' => 'required|valid_date',
    ];

    /** The holiday covering $date for $branchId (branch-specific takes priority over an all-branches one on the same date). */
    public function forDate(string $date, ?int $branchId): ?array
    {
        $query = $this->where('date', $date)->where('status', 'active');
        if ($branchId !== null) {
            $query->groupStart()->where('branch_id', $branchId)->orWhere('branch_id', null)->groupEnd();
            $query->orderBy('branch_id', 'DESC'); // NULLs last in MySQL/MariaDB ASC; DESC puts the branch-specific row first
        } else {
            $query->where('branch_id', null);
        }

        return $query->first();
    }

    public function forMonth(int $year, int $month, ?int $branchId = null): array
    {
        $query = $this->where('YEAR(date)', $year)->where('MONTH(date)', $month)->where('status', 'active');
        if ($branchId !== null) {
            $query->groupStart()->where('branch_id', $branchId)->orWhere('branch_id', null)->groupEnd();
        }

        return $query->orderBy('date')->findAll();
    }

    /**
     * All holidays in $year — used by both the list view's year filter and
     * AttendanceHolidayService::proposeNextYear(). Columns are qualified with
     * the table name because proposeNextYear() calls this after its own
     * select()/join() against `branches`, which has its own `status` column —
     * an unqualified `status` there is ambiguous SQL.
     */
    public function forYear(int $year, bool $activeOnly = true): array
    {
        $query = $this->where('YEAR(attendance_holidays.date)', $year);
        if ($activeOnly) {
            $query->where('attendance_holidays.status', 'active');
        }

        return $query->orderBy('attendance_holidays.date')->findAll();
    }
}
