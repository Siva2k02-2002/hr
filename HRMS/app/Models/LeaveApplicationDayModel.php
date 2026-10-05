<?php

namespace App\Models;

use CodeIgniter\Model;

/** Fully recomputed/reinserted by LeaveCalculationService whenever an application is (re)calculated — never hand-edited. */
class LeaveApplicationDayModel extends Model
{
    protected $table         = 'leave_application_days';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'leave_application_id', 'leave_date', 'day_type', 'day_category', 'is_sandwiched', 'counts_as_leave',
        'day_value', 'holiday_id',
    ];

    public function forApplication(int $applicationId): array
    {
        return $this->where('leave_application_id', $applicationId)->orderBy('leave_date')->findAll();
    }

    public function deleteForApplication(int $applicationId): void
    {
        $this->where('leave_application_id', $applicationId)->delete();
    }

    /** Is $date already counted as leave for this employee on some other pending/approved application? Used by the sandwich-adjacency check to stitch together separate applications. $excludeApplicationId skips the application currently being (re)calculated, so its own soon-to-be-replaced day rows don't self-reference during an edit. */
    public function countedForEmployeeOnDate(int $employeeId, string $date, ?int $excludeApplicationId = null): bool
    {
        $query = $this->select('leave_application_days.id')
            ->join('leave_applications la', 'la.id = leave_application_days.leave_application_id')
            ->where('la.employee_id', $employeeId)
            ->whereIn('la.status', ['pending', 'approved'])
            ->where('leave_application_days.leave_date', $date)
            ->where('leave_application_days.counts_as_leave', 1);

        if ($excludeApplicationId !== null) {
            $query->where('la.id !=', $excludeApplicationId);
        }

        return $query->first() !== null;
    }
}
