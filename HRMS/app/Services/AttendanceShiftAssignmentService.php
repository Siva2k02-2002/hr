<?php

namespace App\Services;

use App\Models\AttendanceShiftAssignmentModel;
use App\Models\AttendanceShiftModel;
use App\Models\AttendanceSettingModel;
use App\Models\EmployeeModel;
use RuntimeException;

/**
 * One history table, not four — "individual / department / branch / bulk
 * assignment" are just different selection UIs that all funnel into the
 * same per-employee insert, closing off the previous open row first. That's
 * what gives history + future-effective-dating for free.
 */
class AttendanceShiftAssignmentService
{
    private AttendanceShiftAssignmentModel $assignments;

    public function __construct(private AuditService $audit = new AuditService())
    {
        $this->assignments = new AttendanceShiftAssignmentModel(service('tenantContext')->db());
    }

    public function assignEmployee(int $employeeId, int $shiftId, string $effectiveFrom, ?int $createdBy = null, bool $replaceExisting = false): void
    {
        $this->assertShiftAssignable($shiftId);

        if ($replaceExisting) {
            $this->clearFutureAssignments($employeeId, $effectiveFrom);
        } else {
            $this->assertNotOverlapping($employeeId, $effectiveFrom);
        }
        $this->closeOpenAssignment($employeeId, $effectiveFrom);

        $id = $this->assignments->insert([
            'employee_id'    => $employeeId,
            'shift_id'       => $shiftId,
            'effective_from' => $effectiveFrom,
            'effective_to'   => null,
            'created_by'     => $createdBy,
            'created_at'     => date('Y-m-d H:i:s'),
        ], true);

        $this->audit->log('shift_assign', 'attendance', 'attendance_shift_assignment', $id, null, [
            'employee_id' => $employeeId, 'shift_id' => $shiftId, 'effective_from' => $effectiveFrom,
        ]);
    }

    /**
     * @param int[] $employeeIds
     * @return array{assigned:int, skipped:int} skipped = employees whose existing
     *         assignment already starts on/after $effectiveFrom (see assertNotOverlapping) —
     *         one conflicting employee must not abort the rest of the batch.
     */
    public function assignBulk(array $employeeIds, int $shiftId, string $effectiveFrom, ?int $createdBy = null, bool $replaceExisting = false): array
    {
        $assigned = 0;
        $skipped  = 0;

        foreach (array_unique($employeeIds) as $employeeId) {
            try {
                $this->assignEmployee((int) $employeeId, $shiftId, $effectiveFrom, $createdBy, $replaceExisting);
                $assigned++;
            } catch (RuntimeException) {
                $skipped++;
            }
        }

        return ['assigned' => $assigned, 'skipped' => $skipped];
    }

    public function assignByDepartment(int $departmentId, int $shiftId, string $effectiveFrom, ?int $createdBy = null, bool $replaceExisting = false): array
    {
        return $this->assignBulk($this->activeEmployeeIds('department_id', $departmentId), $shiftId, $effectiveFrom, $createdBy, $replaceExisting);
    }

    public function assignByBranch(int $branchId, int $shiftId, string $effectiveFrom, ?int $createdBy = null, bool $replaceExisting = false): array
    {
        return $this->assignBulk($this->activeEmployeeIds('branch_id', $branchId), $shiftId, $effectiveFrom, $createdBy, $replaceExisting);
    }

    public function assignByDesignation(int $designationId, int $shiftId, string $effectiveFrom, ?int $createdBy = null, bool $replaceExisting = false): array
    {
        return $this->assignBulk($this->activeEmployeeIds('designation_id', $designationId), $shiftId, $effectiveFrom, $createdBy, $replaceExisting);
    }

    public function assignByEmploymentType(string $employmentType, int $shiftId, string $effectiveFrom, ?int $createdBy = null, bool $replaceExisting = false): array
    {
        return $this->assignBulk($this->activeEmployeeIds('employment_type', $employmentType), $shiftId, $effectiveFrom, $createdBy, $replaceExisting);
    }

    public function assignByEmploymentCategory(string $employmentCategory, int $shiftId, string $effectiveFrom, ?int $createdBy = null, bool $replaceExisting = false): array
    {
        return $this->assignBulk($this->activeEmployeeIds('employment_category', $employmentCategory), $shiftId, $effectiveFrom, $createdBy, $replaceExisting);
    }

    /** What shift employee $employeeId is on for $date — their own assignment history, else the company default. */
    public function resolveShiftFor(int $employeeId, string $date): ?array
    {
        $assignment = $this->assignments->currentFor($employeeId, $date);
        if ($assignment) {
            return [
                'id' => (int) $assignment['shift_id'], 'name' => $assignment['shift_name'], 'code' => $assignment['shift_code'],
                'start_time' => $assignment['start_time'], 'end_time' => $assignment['end_time'],
                'grace_minutes' => (int) $assignment['grace_minutes'], 'full_day_minutes' => (int) $assignment['full_day_minutes'],
                'is_night_shift' => (bool) $assignment['is_night_shift'], 'effective_from' => $assignment['effective_from'], 'source' => 'assigned',
            ];
        }

        $settings = (new AttendanceSettingModel(service('tenantContext')->db()))->current();
        if (! $settings['default_shift_id']) {
            return null;
        }

        $shift = (new AttendanceShiftModel(service('tenantContext')->db()))->find($settings['default_shift_id']);
        if (! $shift) {
            return null;
        }

        return [
            'id' => (int) $shift['id'], 'name' => $shift['name'], 'code' => $shift['code'],
            'start_time' => $shift['start_time'], 'end_time' => $shift['end_time'],
            'grace_minutes' => (int) $shift['grace_minutes'], 'full_day_minutes' => (int) $shift['full_day_minutes'],
            'is_night_shift' => (bool) $shift['is_night_shift'], 'effective_from' => null, 'source' => 'default',
        ];
    }

    /** A shift that's been deactivated or deleted must never receive new assignments — existing ones are untouched (history). */
    private function assertShiftAssignable(int $shiftId): void
    {
        $shift = (new AttendanceShiftModel(service('tenantContext')->db()))->find($shiftId);
        if (! $shift) {
            throw new RuntimeException('That shift no longer exists.');
        }
        if ($shift['status'] !== 'active') {
            throw new RuntimeException("The \"{$shift['name']}\" shift is inactive and cannot be assigned.");
        }
    }

    private function activeEmployeeIds(string $column, int|string $value): array
    {
        return array_column(
            (new EmployeeModel(service('tenantContext')->db()))->select('id')->where($column, $value)->where('status !=', 'terminated')->findAll(),
            'id'
        );
    }

    private function closeOpenAssignment(int $employeeId, string $newEffectiveFrom): void
    {
        $open = $this->assignments->where('employee_id', $employeeId)->where('effective_to', null)->orderBy('effective_from', 'DESC')->first();
        if ($open) {
            $dayBefore = date('Y-m-d', strtotime($newEffectiveFrom . ' -1 day'));
            $this->assignments->update($open['id'], ['effective_to' => $dayBefore]);
        }
    }

    /**
     * "Replace existing" bulk mode: drop any not-yet-effective assignments this employee already
     * has from $fromDate onward before inserting the new one — unlike the default skip-on-conflict
     * path, the admin has explicitly said the new assignment should win. Never touches assignments
     * that already started (effective_from < $fromDate); those are past/current history and are
     * closed off by closeOpenAssignment() instead, never deleted.
     */
    private function clearFutureAssignments(int $employeeId, string $fromDate): void
    {
        $this->assignments->where('employee_id', $employeeId)->where('effective_from >=', $fromDate)->delete();
    }

    /**
     * New assignments are append-only forward in time: reject an effective_from that
     * falls on or before the employee's most recent existing assignment. Without this,
     * closeOpenAssignment() only ever closes the single open-ended row, so a backdated
     * assignment inserted after a later one had already been created would silently
     * corrupt that later row's effective_to (setting it before its own effective_from)
     * and leave two assignments overlapping the same date. Editing a past assignment
     * should go through updating that record directly, not a new insert.
     */
    private function assertNotOverlapping(int $employeeId, string $effectiveFrom): void
    {
        $latest = $this->assignments->where('employee_id', $employeeId)->orderBy('effective_from', 'DESC')->first();
        if ($latest && strtotime($effectiveFrom) <= strtotime($latest['effective_from'])) {
            throw new RuntimeException(
                "This employee already has a shift assignment effective from {$latest['effective_from']}. " .
                'New assignments must take effect after the most recent one — edit that assignment directly to change a past date.'
            );
        }
    }
}
