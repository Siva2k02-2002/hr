<?php

use App\Models\LeaveSettingModel;

if (! function_exists('leave_status_badge_class')) {
    function leave_status_badge_class(string $status): string
    {
        return match ($status) {
            'draft'                => 'badge-muted',
            'pending'               => 'badge-warning',
            'approved'              => 'badge-success',
            'rejected'              => 'badge-danger',
            'cancelled'             => 'badge-muted',
            default                 => 'badge-muted',
        };
    }
}

if (! function_exists('leave_status_label')) {
    function leave_status_label(string $status): string
    {
        return ucfirst($status);
    }
}

if (! function_exists('leave_day_type_label')) {
    function leave_day_type_label(string $dayType): string
    {
        return match ($dayType) {
            'half_first'  => 'First Half',
            'half_second' => 'Second Half',
            default       => 'Full Day',
        };
    }
}

if (! function_exists('leave_financial_year_bounds')) {
    /**
     * [startDate, endDate] for the financial year identified by its starting calendar
     * year, per leave_settings.financial_year_start_month. E.g. start_month=4,
     * $financialYear=2026 -> ['2026-04-01', '2027-03-31']. start_month=1 -> plain
     * calendar year. Every "which FY does this date fall in" / "count applications
     * this FY" check in the Leave module goes through this, never a bare YEAR().
     */
    function leave_financial_year_bounds(int $financialYear, ?int $startMonth = null): array
    {
        $startMonth ??= (int) (new LeaveSettingModel(service('tenantContext')->db()))->current()['financial_year_start_month'];
        $start = sprintf('%04d-%02d-01', $financialYear, $startMonth);
        $endYear = $startMonth === 1 ? $financialYear : $financialYear + 1;
        $endMonthIndex = $startMonth === 1 ? 12 : $startMonth - 1;
        $end = date('Y-m-t', strtotime(sprintf('%04d-%02d-01', $endYear, $endMonthIndex)));

        return [$start, $end];
    }
}

if (! function_exists('leave_financial_year')) {
    /** The starting calendar year of the financial year containing $date (default: today). */
    function leave_financial_year(?string $date = null, ?int $startMonth = null): int
    {
        $date ??= date('Y-m-d');
        $startMonth ??= (int) (new LeaveSettingModel(service('tenantContext')->db()))->current()['financial_year_start_month'];

        $year  = (int) date('Y', strtotime($date));
        $month = (int) date('n', strtotime($date));

        return $month >= $startMonth ? $year : $year - 1;
    }
}
