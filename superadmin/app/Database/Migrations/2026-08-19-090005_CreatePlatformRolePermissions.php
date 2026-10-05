<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePlatformRolePermissions extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'role_id'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'permission_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey(['role_id', 'permission_id']);
        $this->forge->addForeignKey('role_id', 'platform_roles', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('permission_id', 'platform_permissions', 'id', '', 'CASCADE');
        $this->forge->createTable('platform_role_permissions');
    }

    public function down()
    {
        $this->forge->dropTable('platform_role_permissions');
    }
}
