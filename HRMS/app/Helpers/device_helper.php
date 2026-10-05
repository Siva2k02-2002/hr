<?php

if (! function_exists('parse_user_agent')) {
    /**
     * Best-effort browser/OS label for a stored user_agent string — used on the Employee
     * Login Account tab's Session Management and Login History cards. Deliberately simple
     * (a handful of substring checks) rather than a full UA-parsing library: it only needs
     * to produce a friendly label, not power any decision.
     */
    function parse_user_agent(?string $ua): string
    {
        if (! $ua) {
            return 'Unknown device';
        }

        $browser = match (true) {
            str_contains($ua, 'Edg/')     => 'Edge',
            str_contains($ua, 'OPR/')     => 'Opera',
            str_contains($ua, 'Chrome/')  => 'Chrome',
            str_contains($ua, 'Firefox/') => 'Firefox',
            str_contains($ua, 'Safari/') && str_contains($ua, 'Version/') => 'Safari',
            default => 'Unknown browser',
        };

        $platform = match (true) {
            str_contains($ua, 'Windows')        => 'Windows',
            str_contains($ua, 'Mac OS X')        => 'macOS',
            str_contains($ua, 'Android')         => 'Android',
            str_contains($ua, 'iPhone'), str_contains($ua, 'iPad') => 'iOS',
            str_contains($ua, 'Linux')           => 'Linux',
            default => 'Unknown OS',
        };

        return $browser . ' on ' . $platform;
    }
}
