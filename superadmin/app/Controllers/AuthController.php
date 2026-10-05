<?php

namespace App\Controllers;

use App\Models\LoginLogModel;
use App\Models\PlatformUserModel;
use App\Services\PermissionService;

class AuthController extends BaseController
{
    public function showLogin()
    {
        if (session('platform_user_id')) {
            return redirect()->to(site_url('dashboard'));
        }

        return view('auth/login', ['title' => 'Sign in']);
    }

    public function login()
    {
        $email    = trim((string) $this->request->getPost('email'));
        $password = (string) $this->request->getPost('password');
        $ip       = $this->request->getIPAddress();

        $rules = ['email' => 'required|valid_email', 'password' => 'required'];
        if (! $this->validateData(['email' => $email, 'password' => $password], $rules)) {
            return view('auth/login', ['title' => 'Sign in', 'errors' => $this->validator->getErrors()]);
        }

        $loginLogs = new LoginLogModel();

        // Belt-and-suspenders throttling: an IP-wide token bucket plus an
        // account-specific lockout, so one doesn't have to compensate for
        // gaps in the other.
        $throttler = service('throttler');
        if ($throttler->check('login_ip_' . md5($ip), 15, MINUTE) === false) {
            return $this->tooManyAttempts($email, $ip, 'Too many login attempts from this network. Please try again shortly.');
        }
        if ($loginLogs->recentFailures($email, 15) >= 5) {
            return $this->tooManyAttempts($email, $ip, 'This account is temporarily locked after repeated failed attempts. Try again in 15 minutes.');
        }

        $userModel = new PlatformUserModel();
        $user      = $userModel->findByEmail($email);

        if (! $user || $user['status'] !== 'active' || ! password_verify($password, $user['password_hash'])) {
            $loginLogs->insert([
                'platform_user_id' => $user['id'] ?? null,
                'email'            => $email,
                'status'           => 'failed',
                'ip_address'       => $ip,
                'user_agent'       => (string) $this->request->getUserAgent(),
                'created_at'       => date('Y-m-d H:i:s'),
            ]);

            return view('auth/login', [
                'title'  => 'Sign in',
                'errors' => ['password' => 'Invalid email or password.'],
            ]);
        }

        session()->regenerate();

        $permissions = (new PermissionService())->slugsForUser((int) $user['id']);

        session()->set([
            'platform_user_id'    => (int) $user['id'],
            'platform_user_name'  => $user['name'],
            'platform_user_email' => $user['email'],
            'platform_permissions'=> $permissions,
        ]);

        $userModel->update($user['id'], ['last_login_at' => date('Y-m-d H:i:s')]);

        $loginLogs->insert([
            'platform_user_id' => $user['id'],
            'email'            => $email,
            'status'           => 'success',
            'ip_address'       => $ip,
            'user_agent'       => (string) $this->request->getUserAgent(),
            'created_at'       => date('Y-m-d H:i:s'),
        ]);

        if ($user['must_change_password']) {
            session()->setFlashdata('warning', 'You are using a temporary password. Please change it from your profile as soon as possible.');
        }

        return redirect()->to(site_url('dashboard'));
    }

    public function logout()
    {
        session()->destroy();

        return redirect()->to(site_url('login'))->with('success', 'You have been logged out.');
    }

    private function tooManyAttempts(string $email, string $ip, string $message)
    {
        (new LoginLogModel())->insert([
            'email'      => $email,
            'status'     => 'failed',
            'ip_address' => $ip,
            'user_agent' => (string) $this->request->getUserAgent(),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return view('auth/login', ['title' => 'Sign in', 'errors' => ['password' => $message]]);
    }
}
