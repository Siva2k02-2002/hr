<?php

namespace App\Services;

use App\Models\CompanyModel;
use CodeIgniter\Database\BaseConnection;
use RuntimeException;
use Throwable;

/**
 * Changes the login email and/or password of ONE company's tenant administrator.
 *
 * Where the account lives: tenant admins are rows of that company's own `users` table
 * (never the platform `platform_users` table), linked to the system role `company-admin`
 * through `user_roles`. The tenant connection comes from TenantConnectionFactory::forCompany().
 *
 * Which account is "the" admin - decided from the data, never from a fixed id:
 *   1. users that hold the `company-admin` role and are not soft-deleted;
 *   2. exactly one of them -> that account;
 *   3. several -> the one whose email equals the platform record's primary_admin_email;
 *   4. otherwise (none, several with no unique match) -> stop with a message; nothing is written.
 *
 * Only that user's row is updated. Passwords are hashed with password_hash(PASSWORD_DEFAULT),
 * exactly as HRMS does at login (password_verify), and are never logged or audited.
 */
class TenantAdminLoginService
{
    private const PASSWORD_HISTORY = 5;

    public function __construct(
        private TenantConnectionFactory $factory = new TenantConnectionFactory(),
        private AuditService $audit = new AuditService(),
    ) {
    }

    /**
     * Read-only: the admin account that would be changed.
     *
     * @return array{id:int,name:string,email:string,status:string}
     *
     * @throws RuntimeException
     */
    public function describe(int $companyId): array
    {
        return $this->publicView($this->findAdmin($this->factory->forCompany($companyId), $this->company($companyId)));
    }

    /**
     * @return array{errors: array<string,string>, changed: string[]}
     *
     * @throws RuntimeException connection / ambiguity / write failures (messages are safe to show)
     */
    public function update(int $companyId, ?string $newEmail, ?string $newPassword, ?string $confirmPassword): array
    {
        $newEmail    = $newEmail !== null ? strtolower(trim($newEmail)) : '';
        $newPassword = (string) $newPassword;

        $errors = [];
        if ($newEmail === '' && $newPassword === '') {
            return ['errors' => ['email' => 'Enter a new email, a new password, or both.'], 'changed' => []];
        }
        if ($newEmail !== '' && (strlen($newEmail) > 150 || filter_var($newEmail, FILTER_VALIDATE_EMAIL) === false)) {
            $errors['email'] = 'Enter a valid email address (up to 150 characters).';
        }
        if ($newPassword !== '') {
            if ($newPassword !== (string) $confirmPassword) {
                $errors['password_confirm'] = 'The password confirmation does not match.';
            } elseif ($policy = self::passwordPolicyError($newPassword)) {
                $errors['password'] = $policy;
            }
        }
        if ($errors !== []) {
            return ['errors' => $errors, 'changed' => []];
        }

        $company = $this->company($companyId);
        $db      = $this->factory->forCompany($companyId);
        $admin   = $this->findAdmin($db, $company);
        $userId  = (int) $admin['id'];

        $emailChanged = $newEmail !== '' && strcasecmp($newEmail, (string) $admin['email']) !== 0;
        if ($emailChanged) {
            // Every row counts, soft-deleted ones included: the unique index on users.email covers them too.
            $taken = $db->table('users')->where('email', $newEmail)->where('id !=', $userId)->countAllResults();
            if ($taken > 0) {
                return ['errors' => ['email' => 'That email is already used by another user in this company.'], 'changed' => []];
            }
        }

        $passwordChanged = $newPassword !== '';
        if ($passwordChanged) {
            foreach ($this->recentHashes($db, $userId, (string) $admin['password_hash']) as $hash) {
                if (password_verify($newPassword, $hash)) {
                    return ['errors' => ['password' => 'That password was used recently. Choose a different one.'], 'changed' => []];
                }
            }
        }

        if (! $emailChanged && ! $passwordChanged) {
            return ['errors' => [], 'changed' => []];
        }

        $now    = date('Y-m-d H:i:s');
        $update = ['updated_at' => $now];
        if ($emailChanged) {
            // The platform operator is vouching for this address, so it must not lock the admin out
            // behind an email-verification step that was meant for the old address.
            $update += ['email' => $newEmail, 'email_verified_at' => $now,
                        'email_verification_token_hash' => null, 'email_verification_expires_at' => null];
        }
        $newHash = null;
        if ($passwordChanged) {
            $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
            $update += [
                'password_hash'          => $newHash,
                'must_change_password'   => 1,            // an operator-set password is temporary
                'password_changed_at'    => $now,
                'session_version'        => (int) $admin['session_version'] + 1,   // signs out other sessions
                'failed_login_attempts'  => 0,
                'locked_until'           => null,
                'locked_reason'          => null,
                'reset_token_hash'       => null,
                'reset_token_expires_at' => null,
            ];
        }

        $db->transBegin();

        try {
            $db->table('users')->where('id', $userId)->update($update);

            if ($passwordChanged) {
                $db->table('password_history')->insert(['user_id' => $userId, 'password_hash' => $newHash, 'created_at' => $now]);
                $db->table('remember_tokens')->where('user_id', $userId)->delete();
            }

            $db->transCommit();
        } catch (Throwable $e) {
            $db->transRollback();
            $db->resetTransStatus();
            log_message('error', 'Tenant admin login update failed for company {id}: {msg}', ['id' => $companyId, 'msg' => $e->getMessage()]);

            throw new RuntimeException('The company database rejected the change. Nothing was changed.');
        }

        // The platform record of the admin email is informational (reminders, listings); keep it aligned.
        if ($emailChanged) {
            try {
                (new CompanyModel())->update($companyId, ['primary_admin_email' => $newEmail]);
            } catch (Throwable $e) {
                log_message('error', 'Could not mirror admin email to the platform record for company {id}.', ['id' => $companyId]);
            }
        }

        $changed = array_values(array_filter([$emailChanged ? 'email' : null, $passwordChanged ? 'password' : null]));
        $this->audit->log(
            'update',
            'tenant_admin_login',
            'tenant_user',
            $userId,
            $emailChanged ? ['email' => $admin['email']] : null,
            ['changed' => $changed] + ($emailChanged ? ['email' => $newEmail] : []),   // never the password or its hash
            $companyId
        );

        return ['errors' => [], 'changed' => $changed];
    }

    /** Same rules as HRMS PasswordPolicyService::assertStrong(), so the new password is acceptable there too. */
    public static function passwordPolicyError(string $password): ?string
    {
        $missing = [];
        if (strlen($password) < 8) {
            $missing[] = 'be at least 8 characters';
        }
        if (! preg_match('/[A-Z]/', $password)) {
            $missing[] = 'contain an uppercase letter';
        }
        if (! preg_match('/[a-z]/', $password)) {
            $missing[] = 'contain a lowercase letter';
        }
        if (! preg_match('/[0-9]/', $password)) {
            $missing[] = 'contain a number';
        }
        if (! preg_match('/[^A-Za-z0-9]/', $password)) {
            $missing[] = 'contain a symbol';
        }

        return $missing === [] ? null : 'Password must ' . implode(', ', $missing) . '.';
    }

    private function company(int $companyId): array
    {
        $company = (new CompanyModel())->find($companyId);
        if (! $company) {
            throw new RuntimeException('Company not found.');
        }

        return $company;
    }

    /** @throws RuntimeException when the correct account cannot be determined safely */
    private function findAdmin(BaseConnection $db, array $company): array
    {
        $admins = $db->table('users u')
            ->select('u.*')
            ->join('user_roles ur', 'ur.user_id = u.id')
            ->join('roles r', 'r.id = ur.role_id')
            ->where('r.slug', 'company-admin')
            ->where('u.deleted_at', null)
            ->groupBy('u.id')
            ->get()->getResultArray();

        if (count($admins) === 1) {
            return $admins[0];
        }
        if ($admins === []) {
            throw new RuntimeException('This company has no administrator account with the Company Admin role. Nothing was changed.');
        }

        $primary = strtolower(trim((string) ($company['primary_admin_email'] ?? '')));
        $match   = array_values(array_filter($admins, static fn ($u) => $primary !== '' && strtolower((string) $u['email']) === $primary));
        if (count($match) === 1) {
            return $match[0];
        }

        throw new RuntimeException('This company has several Company Admin accounts and the main one cannot be identified safely. Change it from inside the company (Users) instead. Nothing was changed.');
    }

    private function publicView(array $admin): array
    {
        return ['id' => (int) $admin['id'], 'name' => (string) $admin['name'], 'email' => (string) $admin['email'], 'status' => (string) $admin['status']];
    }

    /** Current hash plus the last few kept in password_history. */
    private function recentHashes(BaseConnection $db, int $userId, string $currentHash): array
    {
        $rows = $db->table('password_history')->select('password_hash')->where('user_id', $userId)
            ->orderBy('id', 'DESC')->limit(self::PASSWORD_HISTORY)->get()->getResultArray();

        return array_filter(array_merge([$currentHash], array_column($rows, 'password_hash')));
    }
}
