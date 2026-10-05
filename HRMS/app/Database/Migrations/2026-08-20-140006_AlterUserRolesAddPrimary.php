<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * user_roles is already a many-to-many pivot, so "additional roles" needs
 * no schema change — is_primary just marks which one drives the user's
 * headline role in list/profile UI. Phase 3 only exposes assigning a
 * single primary role in the UI; the pivot is ready for multi-role
 * assignment when that UI is built.
 */
class AlterUserRolesAddPrimary extends Migration
{
    public function up()
    {
        $this->forge->addColumn('user_roles', [
            'is_primary' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1, 'after' => 'role_id'],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('user_roles', ['is_primary']);
    }
}
