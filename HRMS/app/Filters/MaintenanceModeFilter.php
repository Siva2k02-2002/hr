<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Site-wide maintenance mode, toggled by app.maintenanceMode in .env — off
 * by default. Runs before TenantResolver (registered ahead of it in the
 * 'before' required list) so it blocks every request, including login,
 * without needing a working tenant/DB connection — useful for exactly the
 * kind of maintenance (a DB migration, a platform DB issue) where tenant
 * resolution itself might be unreliable.
 *
 * Bypass: app.maintenanceBypassToken in .env, passed as ?maintenance_bypass=
 * on the URL — lets ops/QA verify the app behind the scenes while it's up.
 */
class MaintenanceModeFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (! (bool) env('app.maintenanceMode', false)) {
            return null;
        }

        $bypassToken = env('app.maintenanceBypassToken');
        if ($bypassToken && hash_equals($bypassToken, (string) $request->getGet('maintenance_bypass'))) {
            return null;
        }

        return service('response')
            ->setStatusCode(503)
            ->setHeader('Retry-After', '3600')
            ->setBody(view('errors/html/maintenance'));
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return $response;
    }
}
