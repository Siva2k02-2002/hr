<?php

namespace App\Services;

use App\Models\AttendanceShiftModel;
use RuntimeException;

class AttendanceShiftService
{
    private AttendanceShiftModel $shifts;

    public function __construct(private AuditService $audit = new AuditService())
    {
        $this->shifts = new AttendanceShiftModel(service('tenantContext')->db());
    }

    public function create(array $data): int
    {
        $this->assertCodeAvailable($data['code'], null);
        $this->assertValidTimeRange($data);
        $this->assertGraceWithinWorkingHours($data);

        $id = $this->shifts->insert($data);
        $this->audit->log('create', 'attendance', 'attendance_shift', $id, null, $data);

        return $id;
    }

    public function update(int $id, array $data): void
    {
        $this->assertCodeAvailable($data['code'], $id);
        $this->assertValidTimeRange($data);
        $this->assertGraceWithinWorkingHours($data);

        $old = $this->shifts->find($id);
        $this->shifts->update($id, $data);
        $this->audit->log('update', 'attendance', 'attendance_shift', $id, $old, $data);
    }

    public function delete(int $id): void
    {
        $inUse = service('tenantContext')->db()->table('attendance_shift_assignments')->where('shift_id', $id)->countAllResults();
        if ($inUse > 0) {
            throw new RuntimeException('This shift still has employees assigned to it. Reassign them first.');
        }

        $old = $this->shifts->find($id);
        $this->shifts->delete($id);
        $this->audit->log('delete', 'attendance', 'attendance_shift', $id, $old, null);
    }

    private function assertCodeAvailable(string $code, ?int $ignoreId): void
    {
        $existing = $this->shifts->where('code', $code)->first();
        if ($existing && (int) $existing['id'] !== (int) $ignoreId) {
            throw new RuntimeException('This shift code is already in use.');
        }
    }

    /** An end_time on/before start_time is only valid for a night shift crossing midnight — otherwise it's a typo. */
    private function assertValidTimeRange(array $data): void
    {
        if (strtotime($data['end_time']) <= strtotime($data['start_time']) && empty($data['is_night_shift'])) {
            throw new RuntimeException('End time must be after start time — check "Night shift" if this shift crosses midnight.');
        }
    }

    /** Grace minutes wider than the shift itself would let an employee arrive after the shift has already ended and still count as on-time. */
    private function assertGraceWithinWorkingHours(array $data): void
    {
        $graceMinutes = (int) ($data['grace_minutes'] ?? 0);
        $shiftMinutes = $this->shifts->durationMinutes($data);

        if ($graceMinutes >= $shiftMinutes) {
            throw new RuntimeException('Grace minutes cannot exceed the shift\'s total working hours.');
        }
    }
}
