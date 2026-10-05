<?php

use App\Models\CompanySettingModel;
use App\Services\TenantContext;

if (! function_exists('tenant')) {
    function tenant(): TenantContext
    {
        return service('tenantContext');
    }
}

if (! function_exists('company_timezone')) {
    /** The tenant's display timezone (company_settings.timezone) — falls back to UTC if unset. */
    function company_timezone(): string
    {
        static $tz = null;
        if ($tz === null) {
            $settings = (new CompanySettingModel(service('tenantContext')->db()))->current();
            $tz       = $settings['timezone'] ?? 'UTC';
        }

        return $tz;
    }
}

if (! function_exists('company_name')) {
    /**
     * Single source of truth for the company's display name: company_settings.company_name
     * (tenant DB, editable on the Settings page). Falls back to the platform's companies.name
     * (set once at provisioning, immutable from within HRMS) only if a tenant has no settings
     * row yet — that should never happen post-provisioning, but keeps this safe either way.
     */
    function company_name(): string
    {
        return company_branding_settings()['company_name'] ?? tenant()->companyName();
    }
}

if (! function_exists('company_branding_settings')) {
    function company_branding_settings(): array
    {
        static $settings = null;
        $settings ??= (new CompanySettingModel(service('tenantContext')->db()))->current() ?? [];

        return $settings;
    }
}

if (! function_exists('company_branding_url')) {
    /** @param 'logo'|'favicon'|'banner' $kind */
    function company_branding_url(string $kind): ?string
    {
        return ! empty(company_branding_settings()[$kind . '_path']) ? site_url('branding/' . $kind) : null;
    }
}

if (! function_exists('company_accent_color')) {
    function company_accent_color(): ?string
    {
        // primary_color (Super Admin → Branding) wins; accent_color is the legacy
        // Company Settings field, kept so tenants that never set branding still render as before.
        $settings = company_branding_settings();

        foreach ([$settings['primary_color'] ?? null, $settings['accent_color'] ?? null] as $candidate) {
            if ($candidate && preg_match('/^#[0-9a-fA-F]{6}$/', $candidate)) {
                return $candidate;
            }
        }

        return null;
    }
}

if (! function_exists('company_accent_rgba')) {
    /**
     * The company accent color as an rgba() string, for the two surfaces that
     * can't consume CSS custom properties — Dompdf-rendered PDFs and HTML
     * email — falls back to the DB column default (#1f6f5c) so a
     * not-yet-configured tenant still renders a real color, not black.
     */
    function company_accent_rgba(float $alpha = 1.0): string
    {
        $hex = company_accent_color() ?? '#1f6f5c';
        [$r, $g, $b] = sscanf($hex, '#%02x%02x%02x');

        return sprintf('rgba(%d, %d, %d, %s)', $r, $g, $b, $alpha);
    }
}

if (! function_exists('local_time')) {
    /**
     * Converts a datetime string stored in the app's storage timezone
     * (Config\App::$appTimezone, i.e. what date('Y-m-d H:i:s') produces at
     * write time — UTC) into the tenant's display timezone for rendering.
     */
    function local_time(?string $datetime, string $format = 'Y-m-d H:i:s'): string
    {
        if (! $datetime) {
            return '';
        }

        $storageTz = config('App')->appTimezone ?: 'UTC';
        $dt        = new DateTime($datetime, new DateTimeZone($storageTz));
        $dt->setTimezone(new DateTimeZone(company_timezone()));

        return $dt->format($format);
    }
}

if (! function_exists('menu_active_route')) {
    /**
     * Walks the whole (unlimited-depth) menu tree and finds the single route
     * that best matches the current URI: an exact match, or the longest
     * route that the current URI is nested under (segment-boundary safe via
     * url_is()'s own matching, not a naive string prefix check). Returning
     * the longest match is what stops siblings like `attendance` and
     * `attendance/reports` from both claiming the current page.
     */
    function menu_active_route(array $tree): ?string
    {
        $best    = null;
        $bestLen = -1;

        $visit = function (array $items) use (&$visit, &$best, &$bestLen): void {
            foreach ($items as $item) {
                if (isset($item['route'])) {
                    $route = $item['route'];
                    if ((url_is($route) || url_is($route . '/*')) && strlen($route) > $bestLen) {
                        $best    = $route;
                        $bestLen = strlen($route);
                    }
                }
                if (! empty($item['children'])) {
                    $visit($item['children']);
                }
            }
        };
        $visit($tree);

        return $best;
    }
}

if (! function_exists('menu_is_active')) {
    /** Is this exact TenantMenu item the current page? (leaf links only) */
    function menu_is_active(array $item, ?string $activeRoute): bool
    {
        return isset($item['route']) && $item['route'] === $activeRoute;
    }
}

if (! function_exists('menu_breadcrumb_trail')) {
    /**
     * Returns the chain of menu items (group, then leaf) leading to the
     * active route, e.g. [Attendance group, Shifts leaf]. Empty if the
     * current page isn't in the menu at all (e.g. a create/edit sub-page).
     */
    function menu_breadcrumb_trail(array $tree, ?string $activeRoute): array
    {
        if ($activeRoute === null) {
            return [];
        }

        $find = function (array $items, array $path) use (&$find, $activeRoute): ?array {
            foreach ($items as $item) {
                $path[] = $item;
                if (isset($item['route']) && $item['route'] === $activeRoute) {
                    return $path;
                }
                if (! empty($item['children'])) {
                    $result = $find($item['children'], $path);
                    if ($result !== null) {
                        return $result;
                    }
                }
            }

            return null;
        };

        return $find($tree, []) ?? [];
    }
}

if (! function_exists('menu_group_home_route')) {
    /** First route found in a group's subtree — used as the breadcrumb link target for a group label. */
    function menu_group_home_route(array $item): ?string
    {
        if (isset($item['route'])) {
            return $item['route'];
        }
        foreach ($item['children'] ?? [] as $child) {
            $route = menu_group_home_route($child);
            if ($route !== null) {
                return $route;
            }
        }

        return null;
    }
}

if (! function_exists('menu_group_is_open')) {
    /** Does this group (at any depth) contain the current page? Expansion only — never a highlight. */
    function menu_group_is_open(array $item, ?string $activeRoute): bool
    {
        if ($activeRoute === null) {
            return false;
        }
        if (isset($item['route'])) {
            return $item['route'] === $activeRoute;
        }
        foreach ($item['children'] ?? [] as $child) {
            if (menu_group_is_open($child, $activeRoute)) {
                return true;
            }
        }

        return false;
    }
}
