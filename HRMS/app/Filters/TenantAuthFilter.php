<?php

namespace App\Filters;

use App\Models\UserModel;
use App\Services\PermissionService;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Requires a logged-in tenant user AND that the session belongs to the
 * tenant TenantResolver just resolved for this hostname. The second check
 * is what stops a session cookie issued on one tenant from being reused
 * against another — tenant identity always wins over whatever a session
 * happens to say.
 *
 * Also re-validates the account is still active/not session-invalidated
 * on every request (so suspending a user or hitting "log out all other
 * devices" takes effect on their very next click, not next login) and
 * refreshes the cached permission list when it's gone stale.
 */
class TenantAuthFilter implements FilterInterface
{
    /** Hard ceiling on a session's life regardless of activity — forces a fresh login at least this often. */
    private const ABSOLUTE_LIFETIME_SECONDS = 12 * HOUR;

    public function before(RequestInterface $request, $arguments = null)
    {
        // Filters run before Controller::initController() loads $helpers, so
        // the tenant() function isn't autoloaded yet — load it explicitly.
        helper('tenant');

        if (! session('tenant_user_id')) {
            return $this->denyLogin($request, 'Please log in to continue.');
        }

        if ((int) session('tenant_company_id') !== tenant()->companyId()) {
            $this->clearTenantSession();

            return $this->denyLogin($request, 'Your session is no longer valid here. Please log in again.');
        }

        $userModel = new UserModel(service('tenantContext')->db());
        $user      = $userModel->find((int) session('tenant_user_id'));

        if (! $user || $user['status'] !== 'active') {
            $this->clearTenantSession();

            return $this->denyLogin($request, 'Your account no longer has access. Contact your administrator.');
        }

        if ((int) $user['session_version'] !== (int) session('tenant_session_version')) {
            $this->clearTenantSession();

            return $this->denyLogin($request, 'You were logged out because your session was ended on another device.');
        }

        $fingerprint = hash('sha256', $request->getIPAddress() . '|' . (string) $request->getUserAgent());
        if (session('tenant_session_fp') && ! hash_equals((string) session('tenant_session_fp'), $fingerprint)) {
            $this->clearTenantSession();

            return $this->denyLogin($request, 'Your session could not be verified. Please log in again.');
        }

        $loginAt = (int) session('tenant_login_at');
        if ($loginAt && (time() - $loginAt) > self::ABSOLUTE_LIFETIME_SECONDS) {
            $this->clearTenantSession();

            return $this->denyLogin($request, 'Your session has expired. Please log in again.');
        }

        (new PermissionService())->refreshIfStale((int) $user['id']);
        $userModel->update($user['id'], ['last_active_at' => date('Y-m-d H:i:s')]);

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return $response;
    }

    private function clearTenantSession(): void
    {
        session()->remove([
            'tenant_user_id', 'tenant_user_name', 'tenant_user_email', 'tenant_company_id',
            'tenant_permissions', 'tenant_permissions_version', 'tenant_session_version',
        ]);
    }

    private function denyLogin(RequestInterface $request, string $message)
    {
        if ($request->isAJAX() || str_contains((string) $request->getHeaderLine('Accept'), 'application/json')) {
            return service('response')->setStatusCode(401)->setJSON(['status' => 'failed', 'message' => $message]);
        }

        session()->setFlashdata('error', $message);

        return redirect()->to(site_url('login'));
    }
}
