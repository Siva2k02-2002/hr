<?php

namespace App\Services;

use App\Models\AttendanceWeeklyOffModel;
use RuntimeException;

class AttendanceWeeklyOffService
{
    /** 'alternate' = 2nd & 4th occurrence of that weekday in the month — the standard convention. */
    private const PATTERN_TO_NTH = ['first' => 1, 'second' => 2, 'third' => 3, 'fourth' => 4, 'fifth' => 5];

    private AttendanceWeeklyOffModel $rules;

    public function __construct(private AuditService $audit = new AuditService())
    {
        $this->rules = new AttendanceWeeklyOffModel(service('tenantContext')->db());
    }

    public function create(array $data): int
    {
        $id = $this->rules->insert($data);
        $this->audit->log('create', 'attendance', 'attendance_weekly_off', $id, null, $data);

        return $id;
    }

    public function update(int $id, array $data): void
    {
        $old = $this->rules->find($id);
        if (! $old) {
            throw new RuntimeException('Weekly-off rule not found.');
        }

        $this->rules->update($id, $data);
        $this->audit->log('update', 'attendance', 'attendance_weekly_off', $id, $old, $data);
    }

    public function delete(int $id): void
    {
        $old = $this->rules->find($id);
        if (! $old) {
            throw new RuntimeException('Weekly-off rule not found.');
        }

        $this->rules->delete($id);
        $this->audit->log('delete', 'attendance', 'attendance_weekly_off', $id, $old, null);
    }

    /** Used by AttendanceSummaryService to decide status when there are no punches that day. */
    public function isWeeklyOff(?int $branchId, string $date, ?int $shiftId = null): bool
    {
        $dayOfWeek = strtolower(date('l', strtotime($date)));
        $nth       = (int) ceil((int) date('j', strtotime($date)) / 7);

        foreach ($this->rules->rulesFor($branchId, $shiftId) as $rule) {
            if ($rule['day_of_week'] !== $dayOfWeek) {
                continue;
            }

            $pattern = $rule['week_pattern'];
            if ($pattern === 'every') {
                return true;
            }
            if ($pattern === 'alternate' && in_array($nth, [2, 4], true)) {
                return true;
            }
            if (isset(self::PATTERN_TO_NTH[$pattern]) && self::PATTERN_TO_NTH[$pattern] === $nth) {
                return true;
            }
        }

        return false;
    }
}
