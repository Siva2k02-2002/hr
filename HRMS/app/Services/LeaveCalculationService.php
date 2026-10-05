<?php

namespace App\Services;

use App\Models\AttendanceHolidayModel;
use App\Models\EmployeeModel;
use App\Models\LeaveApplicationDayModel;
use App\Models\LeaveSettingModel;
use RuntimeException;

/**
 * Server-side leave-day calculation — the client never gets to say how many
 * days an application counts for. Walks every calendar date in the range,
 * classifies it against the existing (unmodified) AttendanceHolidayModel/
 * AttendanceWeeklyOffService, then applies the company's sandwich-leave
 * policy. See the Phase 6 plan (§2) for the exact edge-case reasoning this
 * mirrors — this is the executable version of that spec.
 */
class LeaveCalculationService
{
    public function __construct(
        private AttendanceWeeklyOffService $weeklyOffs = new AttendanceWeeklyOffService(),
        private AttendanceShiftAssignmentService $shiftAssignments = new AttendanceShiftAssignmentService()
    ) {
    }

    /**
     * @param  array|null $policyRule  the resolved leave_policy_rules row for this employee+leave type, or null
     * @return array{days: array<int, array<string, mixed>>, totalCountableDays: float}
     */
    public function calculate(
        int $employeeId,
        string $fromDate,
        string $toDate,
        bool $isHalfDay = false,
        ?string $halfDaySession = null,
        ?array $policyRule = null,
        ?int $excludeApplicationId = null
    ): array {
        if (strtotime($fromDate) > strtotime($toDate)) {
            throw new RuntimeException('From date must be on or before the to date.');
        }
        if ($isHalfDay && $fromDate !== $toDate) {
            throw new RuntimeException('Half-day leave can only be applied for a single date.');
        }

        $db       = service('tenantContext')->db();
        $employee = (new EmployeeModel($db))->find($employeeId);
        if (! $employee) {
            throw new RuntimeException('Employee not found.');
        }
        $branchId = $employee['branch_id'] ?? null;

        $settings        = (new LeaveSettingModel($db))->current();
        $sandwichEnabled = (bool) $settings['sandwich_leave_enabled']
            && ($policyRule === null || (bool) $policyRule['sandwich_rule_applicable']);

        $holidayModel = new AttendanceHolidayModel($db);
        $dayModel     = new LeaveApplicationDayModel($db);
        $dates        = $this->dateRange($fromDate, $toDate);

        $days = [];
        foreach ($dates as $d) {
            $holiday   = $holidayModel->forDate($d, $branchId);
            $shift     = $this->shiftAssignments->resolveShiftFor($employeeId, $d);
            $isWeekend = $this->weeklyOffs->isWeeklyOff($branchId, $d, $shift['id'] ?? null);
            $category  = $holiday ? 'holiday' : ($isWeekend ? 'weekend' : 'working');

            $dayType = ($isHalfDay && $d === $fromDate) ? $halfDaySession : 'full';
            if ($dayType !== 'full' && $category !== 'working') {
                throw new RuntimeException('Cannot apply half-day leave on a holiday or weekly-off day.');
            }

            $countsAsLeave = $category === 'working';

            $days[$d] = [
                'leave_date'      => $d,
                'day_type'        => $dayType ?: 'full',
                'day_category'    => $category,
                'is_sandwiched'   => 0,
                'counts_as_leave' => $countsAsLeave ? 1 : 0,
                'day_value'       => ! $countsAsLeave ? 0.0 : ($dayType === 'full' ? 1.0 : 0.5),
                'holiday_id'      => $holiday['id'] ?? null,
            ];
        }

        if ($sandwichEnabled) {
            $days = $this->applySandwichRule($days, $dates, $employeeId, $settings, $dayModel, $excludeApplicationId);
        }

        $totalCountableDays = 0.0;
        foreach ($days as $day) {
            if ($day['counts_as_leave']) {
                $totalCountableDays += $day['day_value'];
            }
        }

        return ['days' => array_values($days), 'totalCountableDays' => $totalCountableDays];
    }

    /** @return array<int, string> */
    private function dateRange(string $from, string $to): array
    {
        $dates  = [];
        $cursor = strtotime($from);
        $end    = strtotime($to);
        while ($cursor <= $end) {
            $dates[] = date('Y-m-d', $cursor);
            $cursor  = strtotime('+1 day', $cursor);
        }

        return $dates;
    }

    /**
     * Groups consecutive non-working dates into runs. A run bounded by an
     * "on leave" date on both sides has each of its days individually
     * evaluated against its own category's leave_settings policy toggle
     * (holiday_between_leave_policy / weekly_off_between_leave_policy) — a
     * holiday could count while an adjacent weekend in the same run doesn't,
     * if the two policies differ. A counted sandwiched day is always a full
     * day (1.0), never half.
     */
    private function applySandwichRule(array $days, array $dates, int $employeeId, array $settings, LeaveApplicationDayModel $dayModel, ?int $excludeApplicationId): array
    {
        $n = count($dates);
        $i = 0;

        while ($i < $n) {
            if ($days[$dates[$i]]['day_category'] === 'working') {
                $i++;
                continue;
            }

            $runStart = $i;
            while ($i < $n && $days[$dates[$i]]['day_category'] !== 'working') {
                $i++;
            }
            $runEnd = $i - 1;

            $beforeDate = date('Y-m-d', strtotime($dates[$runStart] . ' -1 day'));
            $afterDate  = date('Y-m-d', strtotime($dates[$runEnd] . ' +1 day'));

            $beforeQualifies = $this->dateIsOnLeave($beforeDate, $days, $employeeId, $dayModel, $excludeApplicationId);
            $afterQualifies  = $this->dateIsOnLeave($afterDate, $days, $employeeId, $dayModel, $excludeApplicationId);

            if ($beforeQualifies && $afterQualifies) {
                for ($k = $runStart; $k <= $runEnd; $k++) {
                    $d        = $dates[$k];
                    $category = $days[$d]['day_category'];
                    $allowed  = $category === 'holiday'
                        ? $settings['holiday_between_leave_policy'] === 'count'
                        : $settings['weekly_off_between_leave_policy'] === 'count';

                    if ($allowed) {
                        $days[$d]['counts_as_leave'] = 1;
                        $days[$d]['day_value']       = 1.0;
                        $days[$d]['is_sandwiched']   = 1;
                    }
                }
            }
        }

        return $days;
    }

    private function dateIsOnLeave(string $date, array $days, int $employeeId, LeaveApplicationDayModel $dayModel, ?int $excludeApplicationId): bool
    {
        if (isset($days[$date])) {
            return $days[$date]['day_category'] === 'working';
        }

        return $dayModel->countedForEmployeeOnDate($employeeId, $date, $excludeApplicationId);
    }
}
