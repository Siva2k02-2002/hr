<?php

namespace App\Controllers;

use App\Models\LoginLogModel;
use App\Models\RememberTokenModel;
use App\Models\UserModel;
use App\Services\AuditService;
use App\Services\PasswordPolicyService;
use App\Services\PermissionService;
use RuntimeException;

class AuthController extends BaseController
{
    private const MAX_FAILED_ATTEMPTS = 5;
    private const LOCK_MINUTES        = 15;
    private const REMEMBER_DAYS       = 30;
    private const RESET_TOKEN_MINUTES = 30;
    private const PASSWORD_MAX_AGE_DAYS = 90;

    public function showLogin()
    {
        if (session('tenant_user_id') && (int) session('tenant_company_id') === tenant()->companyId()) {
            return redirect()->to(site_url('dashboard'));
        }

        if (! session('tenant_user_id') && $this->attemptRememberLogin()) {
            return redirect()->to(site_url('dashboard'));
        }

        return view('auth/login', ['title' => 'Sign in']);
    }

    public function login()
    {
        $email    = trim((string) $this->request->getPost('email'));
        $password = (string) $this->request->getPost('password');
        $remember = (bool) $this->request->getPost('remember');
        $ip       = $this->request->getIPAddress();

        $rules = ['email' => 'required|valid_email', 'password' => 'required'];
        if (! $this->validateData(['email' => $email, 'password' => $password], $rules)) {
            return view('auth/login', ['title' => 'Sign in', 'errors' => $this->validator->getErrors()]);
        }

        $throttler = service('throttler');
        $bucket    = 'login_' . tenant()->companyCode() . '_' . md5($ip);
        if ($throttler->check($bucket, 15, MINUTE) === false) {
            return $this->loginFailed(null, $email, 'Too many login attempts. Please try again shortly.');
        }

        $userModel = new UserModel(service('tenantContext')->db());
        $user      = $userModel->findByEmail($email);

        if ($user && $userModel->isLocked($user)) {
            return $this->loginFailed($user['id'], $email, 'This account is temporarily locked due to repeated failed attempts. Try again later.');
        }

        if ($user && $user['status'] === 'active' && ! (int) ($user['allow_web_login'] ?? 1)) {
            return $this->loginFailed($user['id'], $email, 'Web login is disabled for this account. Contact your administrator.');
        }

        if (! $user || $user['status'] !== 'active' || ! password_verify($password, $user['password_hash'])) {
            if ($user) {
                $attempts = (int) $user['failed_login_attempts'] + 1;
                $update   = ['failed_login_attempts' => $attempts];
                if ($attempts >= self::MAX_FAILED_ATTEMPTS) {
                    $update['locked_until'] = date('Y-m-d H:i:s', strtotime('+' . self::LOCK_MINUTES . ' minutes'));
                }
                $userModel->update($user['id'], $update);
            }

            return $this->loginFailed($user['id'] ?? null, $email, 'Invalid email or password.');
        }

        $requireVerified = (bool) ((new \App\Models\CompanySettingModel(service('tenantContext')->db()))->current()['require_email_verification'] ?? 0);
        if ($requireVerified && ! $user['email_verified_at']) {
            return $this->loginFailed($user['id'], $email, 'Please verify your email address before signing in. Check your inbox, or ask an administrator to resend the verification email.');
        }

        session()->regenerate();

        $permissions = (new PermissionService())->slugsForUser((int) $user['id']);
        $permVersion = (int) (new \App\Models\CompanySettingModel(service('tenantContext')->db()))->current()['permissions_version'] ?? 1;

        session()->set([
            'tenant_user_id'             => (int) $user['id'],
            'tenant_user_name'           => $user['name'],
            'tenant_user_email'          => $user['email'],
            'tenant_company_id'          => tenant()->companyId(),
            'tenant_permissions'         => $permissions,
            'tenant_permissions_version' => $permVersion,
            'tenant_session_version'     => (int) $user['session_version'],
            'tenant_session_fp'          => $this->sessionFingerprint(),
            'tenant_login_at'            => time(),
        ]);

        $userModel->update($user['id'], [
            'failed_login_attempts' => 0,
            'locked_until'          => null,
            'last_login_at'         => date('Y-m-d H:i:s'),
            'login_count'           => (int) ($user['login_count'] ?? 0) + 1,
            'last_login_ip'         => $ip,
            'last_active_at'        => date('Y-m-d H:i:s'),
        ]);

        // redirect() builds a brand-new RedirectResponse, independent of
        // $this->response — a cookie set on $this->response here would be
        // silently dropped once we return a different response object. The
        // cookie is queued and applied to whichever redirect actually fires.
        $rememberCookie = ($remember && (int) ($user['allow_remember_me'] ?? 1)) ? $this->issueRememberCookie((int) $user['id']) : null;

        (new LoginLogModel(service('tenantContext')->db()))->insert([
            'user_id' => $user['id'], 'email' => $email, 'status' => 'success',
            'ip_address' => $ip, 'user_agent' => (string) $this->request->getUserAgent(), 'created_at' => date('Y-m-d H:i:s'),
        ]);

        if ($user['must_change_password']) {
            session()->setFlashdata('warning', 'You are using a temporary password. Please change it now.');

            $response = redirect()->to(site_url('change-password'));

            return $rememberCookie ? $response->setCookie($rememberCookie) : $response;
        }

        if ($user['password_changed_at'] && strtotime($user['password_changed_at']) < strtotime('-' . self::PASSWORD_MAX_AGE_DAYS . ' days')) {
            session()->setFlashdata('warning', 'Your password is over ' . self::PASSWORD_MAX_AGE_DAYS . ' days old. Please consider changing it from your profile.');
        }

        $response = redirect()->to(site_url('dashboard'));

        return $rememberCookie ? $response->setCookie($rememberCookie) : $response;
    }

    public function logout()
    {
        $this->clearRememberCookie();
        session()->destroy();

        // deleteCookie() must be called on the response we actually return —
        // see the note on issueRememberCookie().
        return redirect()->to(site_url('login'))->with('success', 'You have been logged out.')->deleteCookie('remember_token');
    }

    /** "Log out all other devices" — bumps session_version so every other active session fails its next check. */
    public function logoutOtherDevices()
    {
        $userId    = (int) session('tenant_user_id');
        $userModel = new UserModel(service('tenantContext')->db());
        $user      = $userModel->find($userId);
        $newVersion = (int) $user['session_version'] + 1;

        $userModel->update($userId, ['session_version' => $newVersion]);
        (new RememberTokenModel(service('tenantContext')->db()))->where('user_id', $userId)->delete();
        session()->set('tenant_session_version', $newVersion);

        return redirect()->back()->with('success', 'You have been logged out on all other devices.');
    }

    public function showForgotPassword()
    {
        return view('auth/forgot_password', ['title' => 'Forgot password']);
    }

    public function forgotPassword()
    {
        $email = trim((string) $this->request->getPost('email'));
        if (! $this->validateData(['email' => $email], ['email' => 'required|valid_email'])) {
            return view('auth/forgot_password', ['title' => 'Forgot password', 'errors' => $this->validator->getErrors()]);
        }

        $throttler = service('throttler');
        $bucket    = 'forgot_password_' . tenant()->companyCode() . '_' . md5($this->request->getIPAddress());
        if ($throttler->check($bucket, 5, MINUTE) === false) {
            return view('auth/forgot_password', ['title' => 'Forgot password', 'submitted' => true]);
        }

        $userModel = new UserModel(service('tenantContext')->db());
        $user      = $userModel->findByEmail($email);

        // Same response whether or not the account exists, so this response
        // never confirms/denies an email is registered. The token itself is
        // never returned in the HTTP response — it only ever leaves this
        // process inside the emailed link.
        if ($user && $user['status'] === 'active') {
            $token = bin2hex(random_bytes(32));
            $userModel->update($user['id'], [
                'reset_token_hash'       => hash('sha256', $token),
                'reset_token_expires_at' => date('Y-m-d H:i:s', strtotime('+' . self::RESET_TOKEN_MINUTES . ' minutes')),
            ]);

            $resetLink = site_url('reset-password/' . $token);
            $sent      = $this->sendResetEmail($user, $resetLink);

            (new AuditService())->log('password_reset_requested', 'auth', 'user', (int) $user['id'], null, [
                'email' => $email, 'email_sent' => $sent,
            ]);
        }

        return view('auth/forgot_password', ['title' => 'Forgot password', 'submitted' => true]);
    }

    private function sendResetEmail(array $user, string $resetLink): bool
    {
        $email = service('email');
        $email->setTo($user['email']);
        $email->setSubject('Reset your password');
        $email->setMessage(view('emails/password_reset', [
            'name'        => $user['name'],
            'resetLink'   => $resetLink,
            'expiresMins' => self::RESET_TOKEN_MINUTES,
        ]));

        $sent = $email->send();

        if (! $sent) {
            log_message('error', 'Password reset email failed to send to user {id}: {trace}', [
                'id' => $user['id'], 'trace' => $email->printDebugger(['headers']),
            ]);
        }

        return $sent;
    }

    public function showResetPassword(string $token)
    {
        $userModel = new UserModel(service('tenantContext')->db());
        $user      = $userModel->findByResetToken(hash('sha256', $token));

        if (! $user) {
            return view('auth/reset_password', ['title' => 'Reset password', 'invalid' => true]);
        }

        return view('auth/reset_password', ['title' => 'Reset password', 'token' => $token]);
    }

    public function resetPassword(string $token)
    {
        if ($this->rateLimited('reset_password', 10, MINUTE)) {
            return $this->rateLimitedResponse('Too many attempts. Please try again shortly.');
        }

        $userModel = new UserModel(service('tenantContext')->db());
        $user      = $userModel->findByResetToken(hash('sha256', $token));

        if (! $user) {
            return view('auth/reset_password', ['title' => 'Reset password', 'invalid' => true]);
        }

        $rules = ['password' => 'required|min_length[8]', 'password_confirm' => 'required|matches[password]'];
        if (! $this->validate($rules)) {
            return view('auth/reset_password', ['title' => 'Reset password', 'token' => $token, 'errors' => $this->validator->getErrors()]);
        }

        $password = (string) $this->request->getPost('password');
        $policy   = new PasswordPolicyService();

        try {
            $policy->assertStrong($password);
            $policy->assertNotReused((int) $user['id'], $password);
        } catch (RuntimeException $e) {
            return view('auth/reset_password', ['title' => 'Reset password', 'token' => $token, 'errors' => ['password' => $e->getMessage()]]);
        }

        $newHash = password_hash($password, PASSWORD_DEFAULT);
        $userModel->update($user['id'], [
            'password_hash'          => $newHash,
            'must_change_password'   => 0,
            'password_changed_at'    => date('Y-m-d H:i:s'),
            'reset_token_hash'       => null,
            'reset_token_expires_at' => null,
            'session_version'        => (int) $user['session_version'] + 1,
            'failed_login_attempts'  => 0,
            'locked_until'           => null,
        ]);
        $policy->record((int) $user['id'], $newHash);
        (new RememberTokenModel(service('tenantContext')->db()))->where('user_id', $user['id'])->delete();

        return redirect()->to(site_url('login'))->with('success', 'Your password has been reset. Please sign in.');
    }

    public function showChangePassword()
    {
        return view('auth/change_password', ['title' => 'Change password']);
    }

    public function changePassword()
    {
        $userModel = new UserModel(service('tenantContext')->db());
        $user      = $userModel->find((int) session('tenant_user_id'));

        $rules = [
            'current_password' => 'required',
            'password'         => 'required|min_length[8]',
            'password_confirm' => 'required|matches[password]',
        ];
        if (! $this->validate($rules)) {
            return view('auth/change_password', ['title' => 'Change password', 'errors' => $this->validator->getErrors()]);
        }

        if (! password_verify($this->request->getPost('current_password'), $user['password_hash'])) {
            return view('auth/change_password', ['title' => 'Change password', 'errors' => ['current_password' => 'Current password is incorrect.']]);
        }

        $password = (string) $this->request->getPost('password');
        $policy   = new PasswordPolicyService();

        try {
            $policy->assertStrong($password);
            $policy->assertNotReused((int) $user['id'], $password);
        } catch (RuntimeException $e) {
            return view('auth/change_password', ['title' => 'Change password', 'errors' => ['password' => $e->getMessage()]]);
        }

        $newVersion = (int) $user['session_version'] + 1;
        $newHash    = password_hash($password, PASSWORD_DEFAULT);

        $userModel->update($user['id'], [
            'password_hash'       => $newHash,
            'must_change_password'=> 0,
            'password_changed_at' => date('Y-m-d H:i:s'),
            'session_version'     => $newVersion,
        ]);
        $policy->record((int) $user['id'], $newHash);
        (new RememberTokenModel(service('tenantContext')->db()))->where('user_id', $user['id'])->delete();
        session()->set('tenant_session_version', $newVersion);

        return redirect()->to(site_url('dashboard'))->with('success', 'Password changed successfully.');
    }

    private function loginFailed(?int $userId, string $email, string $message)
    {
        (new LoginLogModel(service('tenantContext')->db()))->insert([
            'user_id' => $userId, 'email' => $email, 'status' => 'failed',
            'ip_address' => $this->request->getIPAddress(), 'user_agent' => (string) $this->request->getUserAgent(), 'created_at' => date('Y-m-d H:i:s'),
        ]);

        return view('auth/login', ['title' => 'Sign in', 'errors' => ['password' => $message]]);
    }

    /** @return array cookie definition — apply with ->setCookie() on whatever response is actually returned */
    private function issueRememberCookie(int $userId): array
    {
        $selector  = bin2hex(random_bytes(12));
        $validator = bin2hex(random_bytes(32));

        (new RememberTokenModel(service('tenantContext')->db()))->insert([
            'user_id'        => $userId,
            'selector'       => $selector,
            'validator_hash' => hash('sha256', $validator),
            'ip_address'     => $this->request->getIPAddress(),
            'user_agent'     => (string) $this->request->getUserAgent(),
            'expires_at'     => date('Y-m-d H:i:s', strtotime('+' . self::REMEMBER_DAYS . ' days')),
            'created_at'     => date('Y-m-d H:i:s'),
        ]);

        return [
            'name'     => 'remember_token',
            'value'    => $selector . ':' . $validator,
            'expire'   => self::REMEMBER_DAYS * DAY,
            'httponly' => true,
            'samesite' => 'Lax',
        ];
    }

    private function attemptRememberLogin(): bool
    {
        $cookie = $this->request->getCookie('remember_token');
        if (! $cookie || ! str_contains($cookie, ':')) {
            return false;
        }

        [$selector, $validator] = explode(':', $cookie, 2);
        $tokenModel = new RememberTokenModel(service('tenantContext')->db());
        $token      = $tokenModel->findValid($selector);

        if (! $token || ! hash_equals($token['validator_hash'], hash('sha256', $validator))) {
            return false;
        }

        $userModel = new UserModel(service('tenantContext')->db());
        $user      = $userModel->find($token['user_id']);
        if (! $user || $user['status'] !== 'active') {
            return false;
        }

        session()->regenerate();
        session()->set([
            'tenant_user_id'             => (int) $user['id'],
            'tenant_user_name'           => $user['name'],
            'tenant_user_email'          => $user['email'],
            'tenant_company_id'          => tenant()->companyId(),
            'tenant_permissions'         => (new PermissionService())->slugsForUser((int) $user['id']),
            'tenant_permissions_version' => (int) (new \App\Models\CompanySettingModel(service('tenantContext')->db()))->current()['permissions_version'] ?? 1,
            'tenant_session_version'     => (int) $user['session_version'],
            'tenant_session_fp'          => $this->sessionFingerprint(),
            'tenant_login_at'            => time(),
        ]);
        $userModel->update($user['id'], ['last_active_at' => date('Y-m-d H:i:s')]);

        return true;
    }

    /** Binds a session to the browser that created it — IP + User-Agent, hashed. Checked on every request by TenantAuthFilter. */
    private function sessionFingerprint(): string
    {
        return hash('sha256', $this->request->getIPAddress() . '|' . (string) $this->request->getUserAgent());
    }

    /** Removes the DB-side token; the cookie itself is cleared by logout()'s ->deleteCookie() on its redirect. */
    private function clearRememberCookie(): void
    {
        $cookie = $this->request->getCookie('remember_token');
        if ($cookie && str_contains($cookie, ':')) {
            [$selector] = explode(':', $cookie, 2);
            (new RememberTokenModel(service('tenantContext')->db()))->where('selector', $selector)->delete();
        }
    }
}
