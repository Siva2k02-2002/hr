<?php

namespace App\Models;

use CodeIgniter\Model;

/** Every raw punch event — append-only. */
class AttendanceLogModel extends Model
{
    protected $table         = 'attendance_logs';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'employee_id', 'attendance_id', 'punch_type', 'punch_time', 'latitude', 'longitude', 'accuracy_meters',
        'device_id', 'location_id', 'distance_meters', 'geofence_status', 'ip_address', 'source', 'remarks', 'created_at',
    ];

    public function forEmployeeAndDate(int $employeeId, string $date): array
    {
        return $this->where('employee_id', $employeeId)
            ->where('DATE(punch_time)', $date)
            ->orderBy('punch_time', 'ASC')
            ->findAll();
    }

    /**
     * Logs for a shift that starts on $date and crosses midnight (e.g. 10 PM -> 6 AM):
     * a noon-to-noon window so the evening punch-in and the following morning's
     * punch-out both land under the same shift date instead of splitting across
     * two calendar days.
     */
    public function forShiftDate(int $employeeId, string $date): array
    {
        return $this->where('employee_id', $employeeId)
            ->where('punch_time >=', $date . ' 12:00:00')
            ->where('punch_time <', date('Y-m-d', strtotime($date . ' +1 day')) . ' 12:00:00')
            ->orderBy('punch_time', 'ASC')
            ->findAll();
    }

    /**
     * True if the employee's most recent punch overall was an 'in' with no matching
     * 'out' yet. Deliberately not date-scoped: a night shift's punch-in happens on one
     * calendar day and its punch-out on the next, so restricting to "today" would fail
     * to recognize the open punch-in and let the employee punch 'in' again instead of
     * closing it out.
     */
    public function hasOpenPunchIn(int $employeeId): bool
    {
        $last = $this->mostRecentPunch($employeeId);

        return $last !== null && $last['punch_type'] === 'in';
    }

    public function mostRecentPunch(int $employeeId): ?array
    {
        return $this->where('employee_id', $employeeId)
            ->orderBy('punch_time', 'DESC')
            ->first();
    }

    /**
     * Same as mostRecentPunch() but takes a row lock (FOR UPDATE) — must be called inside an
     * open transaction. Closes the check-then-insert race that let two near-simultaneous punches
     * (a double-click, a network retry, the same account open in two tabs/devices) both read "no
     * open punch-in" and both insert an 'in' row for the same day before either commits.
     */
    public function mostRecentPunchForUpdate(int $employeeId): ?array
    {
        $builder = $this->builder();
        $builder->where('employee_id', $employeeId)->orderBy('punch_time', 'DESC')->limit(1);

        return $this->db->query($builder->getCompiledSelect() . ' FOR UPDATE')->getRowArray();
    }

    public function recentFor(int $employeeId, int $limit = 10): array
    {
        return $this->select('attendance_logs.*, l.name as location_name')
            ->join('attendance_locations l', 'l.id = attendance_logs.location_id', 'left')
            ->where('attendance_logs.employee_id', $employeeId)
            ->orderBy('punch_time', 'DESC')
            ->findAll($limit);
    }
}
