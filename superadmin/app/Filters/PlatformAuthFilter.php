<?php

namespace App\Filters;

use App\Models\PlatformUserModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class PlatformAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (! session('platform_user_id')) {
            session()->setFlashdata('error', 'Please log in to continue.');

            return redirect()->to(site_url('login'));
        }

        // Re-checked on every request, not just at login — otherwise deactivating a
        // platform user (PlatformUsersController::toggle()) has no effect on a
        // session that was already established before the deactivation.
        $user = (new PlatformUserModel())->find((int) session('platform_user_id'));
        if (! $user || $user['status'] !== 'active') {
            session()->destroy();
            session()->setFlashdata('error', 'Your account no longer has access. Contact your administrator.');

            return redirect()->to(site_url('login'));
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return $response;
    }
}
