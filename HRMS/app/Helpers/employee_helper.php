<?php

if (! function_exists('employee_status_badge_class')) {
    function employee_status_badge_class(string $status): string
    {
        return match ($status) {
            'active'                              => 'badge-success',
            'probation'                            => 'badge-info',
            'notice_period'                        => 'badge-warning',
            'suspended'                             => 'badge-warning',
            'resigned', 'relieved', 'retired'       => 'badge-muted',
            'terminated', 'absconded'               => 'badge-danger',
            default                                 => 'badge-muted',
        };
    }
}

if (! function_exists('employee_status_label')) {
    function employee_status_label(string $status): string
    {
        return match ($status) {
            'notice_period' => 'Notice Period',
            default         => ucfirst($status),
        };
    }
}

if (! function_exists('employee_initials')) {
    function employee_initials(array $employee): string
    {
        $first = mb_substr((string) ($employee['first_name'] ?? ''), 0, 1);
        $last  = mb_substr((string) ($employee['last_name'] ?? ''), 0, 1);

        return mb_strtoupper($first . $last) ?: '?';
    }
}

if (! function_exists('mask_account_number')) {
    /** Keeps only the last 4 digits visible — e.g. "••••••1234". Full number is only ever sent to employee.bank.view-authorized requests. */
    function mask_account_number(string $accountNumber): string
    {
        $length = strlen($accountNumber);
        if ($length <= 4) {
            return str_repeat('•', $length);
        }

        return str_repeat('•', $length - 4) . substr($accountNumber, -4);
    }
}
