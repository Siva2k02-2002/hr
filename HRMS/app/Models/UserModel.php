<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Tenant users live in the company's own database. Always instantiate with
 * an explicit tenant connection — `new UserModel(service('tenantContext')->db())` — never rely
 * on the default group, which points at a scratch/dev database only.
 */
class UserModel extends Model
{
    protected $table          = 'users';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $useTimestamps  = true;
    protected $useSoftDeletes = true;
    protected $deletedField   = 'deleted_at';
    protected $allowedFields  = [
        'name', 'username', 'email', 'mobile', 'employee_id', 'branch_id', 'department_id', 'designation_id',
        'password_hash', 'status', 'must_change_password', 'failed_login_attempts', 'locked_until',
        'locked_reason', 'last_login_at', 'login_count', 'last_login_ip', 'last_active_at', 'session_version',
        'allow_remember_me', 'allow_mobile_login', 'allow_web_login', 'require_2fa', 'password_changed_at',
        'reset_token_hash', 'reset_token_expires_at', 'created_by', 'updated_by',
        'email_verified_at', 'email_verification_token_hash', 'email_verification_expires_at',
    ];

    /**
     * No is_unique here on purpose: it always checks against the default
     * DB group, never this model's actual dynamic tenant connection —
     * uniqueness is checked by hand in the controller (see
     * UsersController::uniquenessErrors()) against service('tenantContext')->db() directly.
     */
    protected $validationRules = [
        'name'     => 'required|min_length[2]|max_length[150]',
        'email'    => 'required|valid_email|max_length[150]',
        'username' => 'permit_empty|alpha_numeric|max_length[60]',
        'status'   => 'required|in_list[active,inactive]',
    ];

    public function findByEmail(string $email): ?array
    {
        return $this->where('email', $email)->first();
    }

    public function findByResetToken(string $tokenHash): ?array
    {
        return $this->where('reset_token_hash', $tokenHash)
            ->where('reset_token_expires_at >=', date('Y-m-d H:i:s'))
            ->first();
    }

    public function findByVerificationToken(string $tokenHash): ?array
    {
        return $this->where('email_verification_token_hash', $tokenHash)
            ->where('email_verification_expires_at >=', date('Y-m-d H:i:s'))
            ->first();
    }

    public function roleSlugs(int $userId): array
    {
        $rows = $this->db->table('user_roles ur')
            ->select('r.slug')
            ->join('roles r', 'r.id = ur.role_id')
            ->where('ur.user_id', $userId)
            ->get()
            ->getResultArray();

        return array_column($rows, 'slug');
    }

    public function primaryRole(int $userId): ?array
    {
        return $this->db->table('user_roles ur')
            ->select('r.id, r.name, r.slug')
            ->join('roles r', 'r.id = ur.role_id')
            ->where('ur.user_id', $userId)
            ->orderBy('ur.is_primary', 'DESC')
            ->get()
            ->getRowArray();
    }

    public function withPrimaryRole()
    {
        return $this->select('users.*, r.name as role_name')
            ->join('user_roles ur', 'ur.user_id = users.id AND ur.is_primary = 1', 'left')
            ->join('roles r', 'r.id = ur.role_id', 'left');
    }

    public function isLocked(array $user): bool
    {
        return ! empty($user['locked_until']) && $user['locked_until'] > date('Y-m-d H:i:s');
    }
}
