<?php

namespace App\Controllers;

use App\Services\EmailVerificationService;

class EmailVerificationController extends BaseController
{
    public function verify(string $token)
    {
        if ($this->rateLimited('email_verify', 10, MINUTE)) {
            return $this->response->setStatusCode(429)->setBody('Too many attempts. Please try again shortly.');
        }

        $ok = (new EmailVerificationService())->verify($token);

        return view('auth/email_verified', ['title' => 'Verify email', 'ok' => $ok]);
    }
}
