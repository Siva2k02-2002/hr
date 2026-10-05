<?php

namespace App\Filters;

use App\Services\PermissionService;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class TenantPermissionFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $slugs = array_filter((array) $arguments);

        if ($slugs === []) {
            return null;
        }

        // 'permission:a,b' means "any of a, b" — used by shared lookup
        // endpoints (e.g. the employee picker) that legitimately serve more
        // than one module, each gated by its own permission.
        $service = new PermissionService();
        $allowed = false;
        foreach ($slugs as $slug) {
            if ($service->can($slug)) {
                $allowed = true;
                break;
            }
        }

        if (! $allowed) {
            if ($request->isAJAX() || str_contains((string) $request->getHeaderLine('Accept'), 'application/json')) {
                return service('response')->setStatusCode(403)->setJSON([
                    'status'  => 'failed',
                    'message' => 'You do not have permission to perform this action.',
                ]);
            }

            return service('response')
                ->setStatusCode(403)
                ->setBody(view('errors/tenant_403', ['permission' => $slugs[array_key_first($slugs)]]));
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return $response;
    }
}
