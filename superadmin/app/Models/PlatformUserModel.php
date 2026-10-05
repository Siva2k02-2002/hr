<?php

namespace App\Models;

use CodeIgniter\Model;

class PlatformUserModel extends Model
{
    protected $table            = 'platform_users';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useTimestamps    = true;
    protected $allowedFields    = [
        'name', 'email', 'password_hash', 'status', 'must_change_password', 'last_login_at',
    ];

    protected $validationRules = [
        'name'  => 'required|min_length[2]|max_length[150]',
        'email' => 'required|valid_email|max_length[150]',
        'status'=> 'required|in_list[active,inactive]',
    ];

    public function findByEmail(string $email): ?array
    {
        return $this->where('email', $email)->first();
    }

    public function roleSlugs(int $userId): array
    {
        $rows = $this->db->table('platform_user_roles ur')
            ->select('r.slug')
            ->join('platform_roles r', 'r.id = ur.role_id')
            ->where('ur.user_id', $userId)
            ->get()
            ->getResultArray();

        return array_column($rows, 'slug');
    }
}
