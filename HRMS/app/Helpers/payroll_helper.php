<?php

use App\Models\PayrollSettingModel;

if (! function_exists('payroll_run_status_badge_class')) {
    function payroll_run_status_badge_class(string $status): string
    {
        return match ($status) {
            'draft'     => 'badge-muted',
            'generated' => 'badge-info',
            'approved'  => 'badge-success',
            'locked'    => 'badge-warning',
            'paid'      => 'badge-success',
            'cancelled' => 'badge-danger',
            default     => 'badge-muted',
        };
    }
}

if (! function_exists('payroll_run_status_label')) {
    function payroll_run_status_label(string $status): string
    {
        return ucfirst($status);
    }
}

if (! function_exists('payroll_simple_status_badge_class')) {
    /** For the small pending/approved/paid/active/closed/rejected enums used by loans, advances, bonus, incentives, reimbursements, arrears. */
    function payroll_simple_status_badge_class(string $status): string
    {
        return match ($status) {
            'active', 'approved', 'paid' => 'badge-success',
            'pending'                    => 'badge-warning',
            'rejected', 'foreclosed'     => 'badge-danger',
            'closed', 'skipped', 'hold'  => 'badge-muted',
            default                      => 'badge-muted',
        };
    }
}

if (! function_exists('payroll_month_name')) {
    function payroll_month_name(int $month): string
    {
        return date('F', mktime(0, 0, 0, $month, 1));
    }
}

if (! function_exists('payroll_period_label')) {
    function payroll_period_label(int $month, int $year): string
    {
        return payroll_month_name($month) . ' ' . $year;
    }
}

if (! function_exists('payroll_financial_year')) {
    /** The starting calendar year of the financial year containing $date (default: today), per payroll_settings.financial_year_start_month. */
    function payroll_financial_year(?string $date = null, ?int $startMonth = null): int
    {
        $date ??= date('Y-m-d');
        $startMonth ??= (int) (new PayrollSettingModel(service('tenantContext')->db()))->current()['financial_year_start_month'];

        $year  = (int) date('Y', strtotime($date));
        $month = (int) date('n', strtotime($date));

        return $month >= $startMonth ? $year : $year - 1;
    }
}

if (! function_exists('payroll_format_amount')) {
    function payroll_format_amount(float|string|null $amount, string $currency = 'INR'): string
    {
        $symbol = match ($currency) {
            'INR'   => '₹',
            'USD'   => '$',
            'EUR'   => '€',
            'GBP'   => '£',
            default => $currency . ' ',
        };

        return $symbol . number_format((float) $amount, 2);
    }
}
