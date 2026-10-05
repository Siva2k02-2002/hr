<?php

namespace App\Models;

use CodeIgniter\Model;

class PlatformRoleModel extends Model
{
    protected $table         = 'platform_roles';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['name', 'slug', 'is_system'];

    protected $validationRules = [
        'name' => 'required|min_length[2]|max_length[100]',
        'slug' => 'required|alpha_dash|max_length[100]',
    ];

    public function permissionSlugs(int $roleId): array
    {
        $rows = $this->db->table('platform_role_permissions rp')
            ->select('p.slug')
            ->join('platform_permissions p', 'p.id = rp.permission_id')
            ->where('rp.role_id', $roleId)
            ->get()
            ->getResultArray();

        return array_column($rows, 'slug');
    }

    public function syncPermissions(int $roleId, array $permissionIds): void
    {
        $this->db->table('platform_role_permissions')->where('role_id', $roleId)->delete();

        if ($permissionIds === []) {
            return;
        }

        $rows = array_map(static fn ($id) => [
            'role_id'       => $roleId,
            'permission_id' => (int) $id,
            'created_at'    => date('Y-m-d H:i:s'),
        ], $permissionIds);

        $this->db->table('platform_role_permissions')->insertBatch($rows);
    }
}
