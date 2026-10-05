<?php

namespace App\Filters;

use App\Services\PermissionService;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Route-level enforcement — takes the required permission slug as a filter
 * argument, e.g. `'permission:company.delete'`. Uses the same
 * PermissionService::can() as the view-level `can()` helper, so a route
 * can never be reachable when its matching button is hidden, or vice versa.
 */
class PlatformPermissionFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $slug = $arguments[0] ?? null;

        if ($slug === null) {
            return null;
        }

        if (! (new PermissionService())->can($slug)) {
            return service('response')
                ->setStatusCode(403)
                ->setBody(view('errors/platform_403', ['permission' => $slug]));
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return $response;
    }
}
