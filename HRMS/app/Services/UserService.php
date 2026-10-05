<?php

namespace App\Services;

use App\Models\CompanySettingModel;
use App\Models\EmployeeModel;
use App\Models\RememberTokenModel;
use App\Models\RoleModel;
use App\Models\UserModel;
use RuntimeException;

class UserService
{
    private const RESET_TOKEN_MINUTES = 30;

    private UserModel $users;

    public function __construct(private AuditService $audit = new AuditService())
    {
        $this->users = new UserModel(service('tenantContext')->db());
    }

    private function findOrFail(int $userId): array
    {
        $user = $this->users->find($userId);
        if (! $user) {
            throw new RuntimeException('That user account was not found, or has been deleted.');
        }

        return $user;
    }

    /** Kills every existing session for this user (bump session_version) and revokes all remember-me tokens. */
    private function invalidateSessions(int $userId): void
    {
        $user = $this->users->find($userId);
        $this->users->update($userId, ['session_version' => (int) ($user['session_version'] ?? 1) + 1]);
        (new RememberTokenModel(service('tenantContext')->db()))->where('user_id', $userId)->delete();
    }

    /**
     * $linkEmployeeId, when given, links this new user to an existing (not yet linked) employee —
     * i.e. sets employees.user_id and overwrites the free-text users.employee_id with that
     * employee's code — inside the same transaction as the user insert.
     */
    public function create(array $data, int $roleId, string $password, int $actorId, ?int $linkEmployeeId = null): int
    {
        $this->assertCanAssignRole($roleId, $actorId);

        if ($linkEmployeeId !== null) {
            $this->assertEmployeeNotLinked($linkEmployeeId, null);
        }

        $db = service('tenantContext')->db();
        $db->transStart();

        try {
            $newHash = password_hash($password, PASSWORD_DEFAULT);
            $userId  = $this->users->insert([
                'name'                 => $data['name'],
                'username'             => ($data['username'] ?? '') ?: null,
                'email'                => $data['email'],
                'mobile'               => ($data['mobile'] ?? '') ?: null,
                'employee_id'          => ($data['employee_id'] ?? '') ?: null,
                'branch_id'            => ($data['branch_id'] ?? '') ?: null,
                'department_id'        => ($data['department_id'] ?? '') ?: null,
                'designation_id'       => ($data['designation_id'] ?? '') ?: null,
                'status'               => $data['status'],
                'password_hash'        => $newHash,
                'must_change_password' => 1,
                'password_changed_at'  => date('Y-m-d H:i:s'),
                'created_by'           => $actorId,
            ]);

            if (! $userId) {
                throw new RuntimeException($this->firstModelError($this->users) ?? 'Could not create user.');
            }

            (new PasswordPolicyService())->record((int) $userId, $newHash);

            service('tenantContext')->db()->table('user_roles')->insert(['user_id' => $userId, 'role_id' => $roleId, 'is_primary' => 1, 'created_at' => date('Y-m-d H:i:s')]);
            (new CompanySettingModel(service('tenantContext')->db()))->bumpPermissionsVersion();

            if ($linkEmployeeId !== null) {
                $this->linkEmployee($linkEmployeeId, $userId);
            }

            $this->audit->log('create', 'users', 'user', $userId, null, ['name' => $data['name'], 'email' => $data['email']]);
        } catch (RuntimeException $e) {
            $db->transRollback();

            throw $e;
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            throw new RuntimeException('Could not create user.');
        }

        (new EmailVerificationService())->sendFor((int) $userId);

        return $userId;
    }

    /**
     * `users.edit`/`users.create` is also held by HR Manager, so the route permission alone
     * doesn't stop a non-admin from POSTing an arbitrary role_id — including company-admin's —
     * straight past the role picker the UI shows them. Only an actor who already holds
     * company-admin may grant (or leave in place, via a no-op edit) the company-admin role.
     */
    private function assertCanAssignRole(int $roleId, int $actorId): void
    {
        $role = (new RoleModel(service('tenantContext')->db()))->find($roleId);
        if (! $role || $role['slug'] !== 'company-admin') {
            return;
        }

        if (! in_array('company-admin', $this->users->roleSlugs($actorId), true)) {
            throw new RuntimeException('Only a Company Admin can grant the Company Admin role.');
        }
    }

    /** Model::insert()/update() return false on validation failure without throwing — surface the real reason instead of a generic message. */
    private function firstModelError(\CodeIgniter\Model $model): ?string
    {
        $errors = $model->errors();

        return $errors !== [] ? reset($errors) : null;
    }

    /**
     * $linkEmployeeId / $linkEmployeeProvided together express "leave the employee link alone"
     * (provided = false) vs. "set it to this employee id, or unlink if null" (provided = true) —
     * a plain nullable param can't distinguish "no change" from "clear it".
     */
    public function update(int $userId, array $data, ?int $roleId, int $actorId, ?int $linkEmployeeId = null, bool $linkEmployeeProvided = false): void
    {
        if ($roleId !== null) {
            $this->assertCanAssignRole($roleId, $actorId);
        }

        $old = $this->users->find($userId);

        $db = service('tenantContext')->db();
        $db->transStart();

        try {
            $ok = $this->users->update($userId, [
                'name'           => $data['name'],
                'username'       => ($data['username'] ?? '') ?: null,
                'email'          => $data['email'],
                'mobile'         => ($data['mobile'] ?? '') ?: null,
                'employee_id'    => ($data['employee_id'] ?? '') ?: null,
                'branch_id'      => ($data['branch_id'] ?? '') ?: null,
                'department_id'  => ($data['department_id'] ?? '') ?: null,
                'designation_id' => ($data['designation_id'] ?? '') ?: null,
                'status'         => $data['status'],
                'updated_by'     => $actorId,
            ]);

            if (! $ok) {
                throw new RuntimeException($this->firstModelError($this->users) ?? 'Could not update user.');
            }

            if ($roleId !== null) {
                $currentRole = $this->users->primaryRole($userId);
                if (! $currentRole || (int) $currentRole['id'] !== $roleId) {
                    if ($actorId === $userId && $currentRole && $currentRole['slug'] === 'company-admin') {
                        throw new RuntimeException('You cannot remove your own Company Admin role.');
                    }

                    service('tenantContext')->db()->table('user_roles')->where('user_id', $userId)->delete();
                    service('tenantContext')->db()->table('user_roles')->insert(['user_id' => $userId, 'role_id' => $roleId, 'is_primary' => 1, 'created_at' => date('Y-m-d H:i:s')]);
                    (new CompanySettingModel(service('tenantContext')->db()))->bumpPermissionsVersion();
                    $this->audit->log('role_changed', 'users', 'user', $userId, ['role_id' => $currentRole['id'] ?? null], ['role_id' => $roleId]);
                }
            }

            if ($linkEmployeeProvided) {
                $this->syncEmployeeLink($userId, $linkEmployeeId);
            }

            $this->audit->log('update', 'users', 'user', $userId, ['name' => $old['name'], 'email' => $old['email']], ['name' => $data['name'], 'email' => $data['email']]);
        } catch (RuntimeException $e) {
            $db->transRollback();

            throw $e;
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            throw new RuntimeException('Could not update user.');
        }
    }

    private function assertEmployeeNotLinked(int $employeeId, ?int $ignoreUserId): void
    {
        $employee = (new EmployeeModel(service('tenantContext')->db()))->find($employeeId);
        if (! $employee) {
            throw new RuntimeException('Selected employee was not found.');
        }
        if (! empty($employee['user_id']) && (int) $employee['user_id'] !== (int) $ignoreUserId) {
            throw new RuntimeException('This employee already has a login account.');
        }
    }

    private function linkEmployee(int $employeeId, int $userId): void
    {
        $employeeModel = new EmployeeModel(service('tenantContext')->db());
        $employee      = $employeeModel->find($employeeId);

        $employeeModel->update($employeeId, ['user_id' => $userId]);
        $this->users->update($userId, ['employee_id' => $employee['employee_code']]);
    }

    private function syncEmployeeLink(int $userId, ?int $newEmployeeId): void
    {
        $employeeModel = new EmployeeModel(service('tenantContext')->db());
        $current       = $employeeModel->where('user_id', $userId)->first();

        if ($current && $newEmployeeId !== null && (int) $current['id'] === $newEmployeeId) {
            return;
        }

        if ($current) {
            $employeeModel->update($current['id'], ['user_id' => null]);
        }

        if ($newEmployeeId !== null) {
            $this->assertEmployeeNotLinked($newEmployeeId, $userId);
            $this->linkEmployee($newEmployeeId, $userId);
        }
    }

    /**
     * No "can't disable own account" guard here on purpose — this primitive is also called by
     * EmployeeStatusService/EmployeeService when an employee's own status change (e.g. archiving)
     * needs to sync their login, and that automated sync must never be blocked just because the
     * acting admin happens to also be the linked employee. The direct, human-initiated "Disable
     * Login" action guards against self-lockout at the controller instead (UsersController::suspend()).
     */
    public function setStatus(int $userId, string $status, int $actorId): void
    {
        $old = $this->findOrFail($userId);

        $this->users->update($userId, ['status' => $status, 'updated_by' => $actorId]);

        // Disabling login must also kick any session already in progress — a bare status flip
        // alone doesn't stop a browser tab that's still logged in from the last time it worked.
        if ($status !== 'active') {
            $this->invalidateSessions($userId);
        }

        $this->audit->log($status === 'active' ? 'activate' : 'suspend', 'users', 'user', $userId, ['status' => $old['status']], ['status' => $status]);
    }

    /**
     * Generates a random temporary password and shows it to the acting admin exactly once.
     * Also revokes every existing session/remember-token for the user (Section O bug fix):
     * a password reset that leaves old sessions alive defeats the point of resetting it.
     */
    public function resetPassword(int $userId, int $actorId): string
    {
        $this->findOrFail($userId);

        $password = bin2hex(random_bytes(8));
        $newHash  = password_hash($password, PASSWORD_DEFAULT);
        $this->users->update($userId, [
            'password_hash'        => $newHash,
            'must_change_password' => 1,
            'password_changed_at'  => date('Y-m-d H:i:s'),
            'failed_login_attempts'=> 0,
            'locked_until'         => null,
            'locked_reason'        => null,
            'updated_by'           => $actorId,
        ]);
        (new PasswordPolicyService())->record($userId, $newHash);
        $this->invalidateSessions($userId);
        $this->audit->log('password_reset', 'users', 'user', $userId, null, null);

        return $password;
    }

    /**
     * HR/Admin sets a specific password by hand (Section C.2). Same session-revocation
     * guarantee as resetPassword(). Unlike the random temp password above, this one is
     * chosen by a human, so it goes through the same strength/reuse policy self-service
     * password changes do.
     */
    public function setPassword(int $userId, string $password, int $actorId): void
    {
        $this->findOrFail($userId);

        $policy = new PasswordPolicyService();
        $policy->assertStrong($password);
        $policy->assertNotReused($userId, $password);

        $newHash = password_hash($password, PASSWORD_DEFAULT);
        $this->users->update($userId, [
            'password_hash'        => $newHash,
            'must_change_password' => 1,
            'password_changed_at'  => date('Y-m-d H:i:s'),
            'failed_login_attempts'=> 0,
            'locked_until'         => null,
            'locked_reason'        => null,
            'updated_by'           => $actorId,
        ]);
        $policy->record($userId, $newHash);
        $this->invalidateSessions($userId);
        $this->audit->log('password_changed', 'users', 'user', $userId, null, null);
    }

    /** Forces a password change on next login without changing the password itself right now (Section C.4). */
    public function expirePassword(int $userId, int $actorId): void
    {
        $this->findOrFail($userId);

        $this->users->update($userId, ['must_change_password' => 1, 'updated_by' => $actorId]);
        $this->audit->log('password_expired', 'users', 'user', $userId, null, null);
    }

    /**
     * Mirrors AuthController::forgotPassword()'s token logic so an admin can trigger a
     * reset link for a user (Section C.3). The link is emailed straight to the user's own
     * account email — never returned to the acting admin — for the same reason
     * forgotPassword() doesn't expose it: whoever can see it can take over the account.
     */
    public function sendResetLink(int $userId, int $actorId): bool
    {
        $user = $this->findOrFail($userId);

        $token = bin2hex(random_bytes(32));
        $this->users->update($userId, [
            'reset_token_hash'       => hash('sha256', $token),
            'reset_token_expires_at' => date('Y-m-d H:i:s', strtotime('+' . self::RESET_TOKEN_MINUTES . ' minutes')),
            'updated_by'             => $actorId,
        ]);

        $email = service('email');
        $email->setTo($user['email']);
        $email->setSubject('Reset your password');
        $email->setMessage(view('emails/password_reset', [
            'name'        => $user['name'],
            'resetLink'   => site_url('reset-password/' . $token),
            'expiresMins' => self::RESET_TOKEN_MINUTES,
        ]));
        $sent = $email->send();

        if (! $sent) {
            log_message('error', 'Admin-triggered password reset email failed to send to user {id}: {trace}', [
                'id' => $userId, 'trace' => $email->printDebugger(['headers']),
            ]);
        }

        $this->audit->log('reset_link_sent', 'users', 'user', $userId, null, ['email_sent' => $sent]);

        return $sent;
    }

    /** Manual lock, distinct from the automatic 5-failed-attempts lock — requires a reason, stored for audit (Section B). */
    public function lock(int $userId, string $reason, int $actorId): void
    {
        $this->findOrFail($userId);

        if ($actorId === $userId) {
            throw new RuntimeException('You cannot lock your own account.');
        }

        $this->users->update($userId, [
            'locked_until'  => date('Y-m-d H:i:s', strtotime('+100 years')),
            'locked_reason' => $reason,
            'updated_by'    => $actorId,
        ]);
        $this->invalidateSessions($userId);
        $this->audit->log('account_locked', 'users', 'user', $userId, null, ['reason' => $reason]);
    }

    public function unlock(int $userId, int $actorId): void
    {
        $this->findOrFail($userId);

        $this->users->update($userId, ['failed_login_attempts' => 0, 'locked_until' => null, 'locked_reason' => null, 'updated_by' => $actorId]);
        $this->audit->log('account_unlocked', 'users', 'user', $userId, null, null);
    }

    /**
     * Employee → Edit → Login Account tab: lets an authorized admin override the username
     * EmployeeService::generateUsername() assigned at login creation. Uniqueness/format are
     * validated by the caller before this runs (same as every other write here) — this only
     * ever changes the username column, nothing else about the account (email, password, role).
     */
    public function renameUsername(int $userId, string $username, int $actorId): void
    {
        $old = $this->findOrFail($userId);

        $this->users->update($userId, ['username' => $username, 'updated_by' => $actorId]);
        $this->audit->log('username_changed', 'users', 'user', $userId, ['username' => $old['username']], ['username' => $username]);
    }

    /** Admin-facing "log out everywhere" (Section E) — AuthController::logoutOtherDevices() is the self-service equivalent for the current session only. */
    public function revokeAllSessions(int $userId, int $actorId): void
    {
        $this->findOrFail($userId);

        $this->invalidateSessions($userId);
        $this->audit->log('logout_all_devices', 'users', 'user', $userId, null, null);
    }

    /** Revokes a single remembered-device token (Section E "Logout Session" on one row). */
    public function revokeDeviceToken(int $userId, int $tokenId, int $actorId): void
    {
        $this->findOrFail($userId);

        (new RememberTokenModel(service('tenantContext')->db()))->where('user_id', $userId)->where('id', $tokenId)->delete();
        $this->audit->log('session_terminated', 'users', 'user', $userId, null, ['token_id' => $tokenId]);
    }

    /** @param array{allow_remember_me?: bool, allow_mobile_login?: bool, allow_web_login?: bool, require_2fa?: bool} $toggles */
    public function setToggles(int $userId, array $toggles, int $actorId): void
    {
        $this->findOrFail($userId);

        $data = ['updated_by' => $actorId];
        foreach (['allow_remember_me', 'allow_mobile_login', 'allow_web_login', 'require_2fa'] as $key) {
            if (array_key_exists($key, $toggles)) {
                $data[$key] = $toggles[$key] ? 1 : 0;
            }
        }

        $this->users->update($userId, $data);
        $this->audit->log('update', 'users', 'user', $userId, null, $data);
    }

    public function delete(int $userId): void
    {
        $this->users->delete($userId);
        service('tenantContext')->db()->table('user_roles')->where('user_id', $userId)->delete();
        $this->audit->log('delete', 'users', 'user', $userId, null, null);
    }
}
