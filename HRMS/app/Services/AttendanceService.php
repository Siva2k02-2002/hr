<?php

namespace App\Services;

use App\Models\AttendanceModel;
use App\Models\AttendanceSettingModel;
use RuntimeException;

/** Manual single/bulk marking — HR asserting a status directly, bypassing the punch/log pipeline entirely. */
class AttendanceService
{
    private AttendanceModel $attendance;

    public function __construct(private AuditService $audit = new AuditService())
    {
        $this->attendance = new AttendanceModel(service('tenantContext')->db());
    }

    /**
     * $punchIn/$punchOut are 'H:i' (or 'H:i:s') times, optional. When both are given,
     * working_minutes is the actual in/out span; otherwise it falls back to the
     * settings-based full-day default, same as before this param existed.
     */
    public function markManual(int $employeeId, string $date, string $status, ?int $userId, ?string $punchIn = null, ?string $punchOut = null): int
    {
        $settings       = (new AttendanceSettingModel(service('tenantContext')->db()))->current();
        $workingMinutes = in_array($status, ['present', 'work_from_home', 'on_duty'], true) ? (int) $settings['full_day_minutes'] : 0;

        $data = [
            'employee_id' => $employeeId, 'attendance_date' => $date, 'status' => $status,
            'working_minutes' => $workingMinutes, 'source' => 'manual', 'updated_by' => $userId,
        ];

        if ($punchIn) {
            $data['first_punch_in_at'] = $date . ' ' . $punchIn;
        }
        if ($punchOut) {
            $data['last_punch_out_at'] = $date . ' ' . $punchOut;
        }
        if ($punchIn && $punchOut) {
            $minutes = (int) round((strtotime($date . ' ' . $punchOut) - strtotime($date . ' ' . $punchIn)) / 60);
            if ($minutes > 0) {
                $data['working_minutes'] = $minutes;
            }
        }

        $existing = $this->attendance->forEmployeeAndDate($employeeId, $date);
        if ($existing) {
            $this->attendance->update($existing['id'], $data);
            $this->audit->log('manual_update', 'attendance', 'attendance', $existing['id'], $existing, $data);

            return $existing['id'];
        }

        $data['created_by'] = $userId;
        $id                 = $this->attendance->insert($data, true);
        $this->audit->log('manual_create', 'attendance', 'attendance', $id, null, $data);

        return $id;
    }

    /** @param int[] $employeeIds */
    public function markBulk(array $employeeIds, string $date, string $status, ?int $userId): int
    {
        foreach ($employeeIds as $employeeId) {
            $this->markManual((int) $employeeId, $date, $status, $userId);
        }

        return count($employeeIds);
    }

    public function delete(int $id): void
    {
        $old = $this->attendance->find($id);
        if (! $old) {
            throw new RuntimeException('Attendance record not found.');
        }

        $this->attendance->delete($id);
        $this->audit->log('delete', 'attendance', 'attendance', $id, $old, null);
    }
}
