<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use Config\PlatformPermissions;

class PlatformRbacSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');

        // --- Sync permissions from the single source of truth -------------------
        $existing = $this->db->table('platform_permissions')->select('slug')->get()->getResultArray();
        $existingSlugs = array_column($existing, 'slug');

        foreach (PlatformPermissions::catalog() as $module => $actions) {
            foreach ($actions as $action => $description) {
                $slug = "{$module}.{$action}";
                if (in_array($slug, $existingSlugs, true)) {
                    continue;
                }
                $this->db->table('platform_permissions')->insert([
                    'slug'        => $slug,
                    'module'      => $module,
                    'description' => $description,
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ]);
            }
        }

        // --- Super Admin role with every permission ------------------------------
        $role = $this->db->table('platform_roles')->where('slug', 'super-admin')->get()->getRowArray();
        if (! $role) {
            $this->db->table('platform_roles')->insert([
                'name'       => 'Super Admin',
                'slug'       => 'super-admin',
                'is_system'  => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $roleId = $this->db->insertID();
        } else {
            $roleId = $role['id'];
        }

        $allPermissionIds = array_column(
            $this->db->table('platform_permissions')->select('id')->get()->getResultArray(),
            'id'
        );

        $this->db->table('platform_role_permissions')->where('role_id', $roleId)->delete();
        $rows = array_map(static fn ($id) => [
            'role_id'       => $roleId,
            'permission_id' => $id,
            'created_at'    => $now,
        ], $allPermissionIds);
        if ($rows !== []) {
            $this->db->table('platform_role_permissions')->insertBatch($rows);
        }

        // --- Default Super Admin user --------------------------------------------
        $email = 'admin@hrms-platform.local';
        $user  = $this->db->table('platform_users')->where('email', $email)->get()->getRowArray();

        if (! $user) {
            $password = bin2hex(random_bytes(6)); // e.g. "3f9a1c2b8e7d"

            $this->db->table('platform_users')->insert([
                'name'                  => 'Platform Super Admin',
                'email'                 => $email,
                'password_hash'         => password_hash($password, PASSWORD_DEFAULT),
                'status'                => 'active',
                'must_change_password'  => 1,
                'created_at'            => $now,
                'updated_at'            => $now,
            ]);
            $userId = $this->db->insertID();

            $this->db->table('platform_user_roles')->insert([
                'user_id'    => $userId,
                'role_id'    => $roleId,
                'created_at' => $now,
            ]);

            echo "Seeded default Super Admin login:\n";
            echo "  Email:    {$email}\n";
            echo "  Password: {$password}\n";
            echo "  (must_change_password is set — change it after first login)\n";
        } else {
            echo "Super Admin user {$email} already exists — skipped.\n";
        }
    }
}
