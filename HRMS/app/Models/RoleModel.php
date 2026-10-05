<?php

namespace App\Models;

use CodeIgniter\Model;

class RoleModel extends Model
{
    protected $table         = 'roles';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['name', 'slug', 'description', 'is_system', 'status'];

    /** No is_unique here — see UserModel's note on why; the slug is generated server-side, not user input. */
    protected $validationRules = [
        'name' => 'required|min_length[2]|max_length[100]',
        'slug' => 'required|alpha_dash|max_length[100]',
    ];

    public function permissionSlugs(int $roleId): array
    {
        $rows = $this->db->table('role_permissions rp')
            ->select('p.slug')
            ->join('permissions p', 'p.id = rp.permission_id')
            ->where('rp.role_id', $roleId)
            ->get()
            ->getResultArray();

        return array_column($rows, 'slug');
    }

    public function permissionIds(int $roleId): array
    {
        return array_column(
            $this->db->table('role_permissions')->select('permission_id')->where('role_id', $roleId)->get()->getResultArray(),
            'permission_id'
        );
    }

    public function syncPermissions(int $roleId, array $permissionIds): void
    {
        $this->db->table('role_permissions')->where('role_id', $roleId)->delete();

        if ($permissionIds === []) {
            return;
        }

        $rows = array_map(static fn ($id) => [
            'role_id'       => $roleId,
            'permission_id' => (int) $id,
            'created_at'    => date('Y-m-d H:i:s'),
        ], $permissionIds);

        $this->db->table('role_permissions')->insertBatch($rows);
    }

    public function userCount(int $roleId): int
    {
        return $this->db->table('user_roles')->where('role_id', $roleId)->countAllResults();
    }
}
