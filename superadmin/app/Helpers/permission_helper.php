<?php

use App\Services\PermissionService;

if (! function_exists('can')) {
    /**
     * View-level button/menu gating. Backed by the same PermissionService
     * the route filter uses, so a hidden button and a blocked route can
     * never disagree about who is allowed to do what.
     */
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
            'active', 'ready', 'provisioned', 'completed' => 'badge-success',
            'trial', 'provisioning', 'started'    => 'badge-info',
            'expiring', 'pending'                 => 'badge-warning',
            'expired', 'suspended', 'cancelled', 'failed', 'error' => 'badge-danger',
            'inactive'                             => 'badge-muted',
            default                                 => 'badge-muted',
        };
    }
}
