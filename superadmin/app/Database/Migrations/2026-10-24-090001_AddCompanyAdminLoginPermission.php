<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/** Permission for changing a company's tenant-admin email/password from Super Admin. Granted to the super-admin role only; idempotent. */
class AddCompanyAdminLoginPermission extends Migration
{
    private const SLUG = 'company.admin_login';

    public function up()
    {
        $now = date('Y-m-d H:i:s');

        if ($this->db->table('platform_permissions')->where('slug', self::SLUG)->countAllResults() === 0) {
            $this->db->table('platform_permissions')->insert([
                'slug' => self::SLUG, 'module' => 'company',
                'description' => 'Change a company\'s tenant administrator email and password',
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        $permissionId = (int) $this->db->table('platform_permissions')->where('slug', self::SLUG)->get()->getRow()->id;
        $role         = $this->db->table('platform_roles')->where('slug', 'super-admin')->get()->getRowArray();

        if ($role && $this->db->table('platform_role_permissions')->where(['role_id' => $role['id'], 'permission_id' => $permissionId])->countAllResults() === 0) {
            $this->db->table('platform_role_permissions')->insert(['role_id' => $role['id'], 'permission_id' => $permissionId]);
        }
    }

    public function down()
    {
        $row = $this->db->table('platform_permissions')->where('slug', self::SLUG)->get()->getRowArray();
        if ($row) {
            $this->db->table('platform_role_permissions')->where('permission_id', $row['id'])->delete();
            $this->db->table('platform_permissions')->where('id', $row['id'])->delete();
        }
    }
}
