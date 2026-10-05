<?php

use App\Services\PermissionService;

if (! function_exists('can')) {
    function can(string $slug): bool
    {
        static $service;
        $service ??= new PermissionService();

        return $service->can($slug);
    }
}

if (! function_exists('status_badge_class')) {
    function status_badge_class(string $status): string
    {
        return match ($status) {
            'active'                 => 'badge-success',
            'inactive', 'archived'   => 'badge-muted',
            default                  => 'badge-muted',
        };
    }
}
