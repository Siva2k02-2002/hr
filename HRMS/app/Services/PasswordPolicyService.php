<?php

namespace App\Services;

use App\Models\PasswordHistoryModel;
use App\Models\UserModel;
use RuntimeException;

/** Central place both self-service (AuthController) and admin-driven (UserService) password changes enforce strength + reuse rules. */
class PasswordPolicyService
{
    private const MIN_LENGTH  = 8;
    private const HISTORY_LEN = 5;

    private PasswordHistoryModel $history;

    public function __construct()
    {
        $this->history = new PasswordHistoryModel(service('tenantContext')->db());
    }

    /** @throws RuntimeException with a user-facing message */
    public function assertStrong(string $password): void
    {
        $rules = [
            strlen($password) >= self::MIN_LENGTH  => 'be at least ' . self::MIN_LENGTH . ' characters',
            preg_match('/[A-Z]/', $password) === 1  => 'contain an uppercase letter',
            preg_match('/[a-z]/', $password) === 1  => 'contain a lowercase letter',
            preg_match('/[0-9]/', $password) === 1  => 'contain a number',
            preg_match('/[^A-Za-z0-9]/', $password) === 1 => 'contain a symbol',
        ];

        $missing = [];
        foreach ($rules as $met => $requirement) {
            if (! $met) {
                $missing[] = $requirement;
            }
        }

        if ($missing !== []) {
            throw new RuntimeException('Password must ' . implode(', ', $missing) . '.');
        }
    }

    /** @throws RuntimeException if this password matches the current one or any of the last 5 */
    public function assertNotReused(int $userId, string $password): void
    {
        $user = (new UserModel(service('tenantContext')->db()))->find($userId);
        $hashes = $this->history->recentHashes($userId, self::HISTORY_LEN);
        if ($user && $user['password_hash']) {
            array_unshift($hashes, $user['password_hash']);
        }

        foreach ($hashes as $hash) {
            if (password_verify($password, $hash)) {
                throw new RuntimeException('You cannot reuse one of your last ' . self::HISTORY_LEN . ' passwords.');
            }
        }
    }

    /** Call right after a password_hash column write, with the same hash that was stored. */
    public function record(int $userId, string $hash): void
    {
        $this->history->record($userId, $hash, self::HISTORY_LEN);
    }
}
