<?php

namespace App\Services;

use App\Models\UserModel;
use RuntimeException;

/** Same token pattern as password reset: random bytes, only the SHA-256 hash stored, single use, expires. */
class EmailVerificationService
{
    private const TOKEN_MINUTES = 60 * 24;

    private UserModel $users;

    public function __construct(private AuditService $audit = new AuditService())
    {
        $this->users = new UserModel(service('tenantContext')->db());
    }

    /** @return bool whether the email actually sent */
    public function sendFor(int $userId): bool
    {
        $user = $this->users->find($userId);
        if (! $user) {
            throw new RuntimeException('User not found.');
        }
        if ($user['email_verified_at']) {
            throw new RuntimeException('This email address is already verified.');
        }

        $token = bin2hex(random_bytes(32));
        $this->users->update($userId, [
            'email_verification_token_hash' => hash('sha256', $token),
            'email_verification_expires_at' => date('Y-m-d H:i:s', strtotime('+' . self::TOKEN_MINUTES . ' minutes')),
        ]);

        $email = service('email');
        $email->setTo($user['email']);
        $email->setSubject('Verify your email address');
        $email->setMessage(view('emails/email_verification', [
            'name'            => $user['name'],
            'verificationLink'=> site_url('verify-email/' . $token),
            'expiresHours'    => (int) (self::TOKEN_MINUTES / 60),
        ]));
        $sent = $email->send();

        if (! $sent) {
            log_message('error', 'Verification email failed to send to user {id}: {trace}', [
                'id' => $userId, 'trace' => $email->printDebugger(['headers']),
            ]);
        }

        $this->audit->log('email_verification_sent', 'auth', 'user', $userId, null, ['email_sent' => $sent]);

        return $sent;
    }

    public function verify(string $token): bool
    {
        $user = $this->users->findByVerificationToken(hash('sha256', $token));
        if (! $user) {
            return false;
        }

        $this->users->update($user['id'], [
            'email_verified_at'             => date('Y-m-d H:i:s'),
            'email_verification_token_hash' => null,
            'email_verification_expires_at' => null,
        ]);
        $this->audit->log('email_verified', 'auth', 'user', (int) $user['id'], null, null);

        return true;
    }
}
