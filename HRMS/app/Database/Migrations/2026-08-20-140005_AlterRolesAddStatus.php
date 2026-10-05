<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * "Archive" a custom role by setting status='archived' rather than a hard
 * delete or CI4 soft-delete scope — roles are low-cardinality and archived
 * roles must still resolve correctly for any user historically assigned
 * to them, so they stay queryable by default.
 */
class AlterRolesAddStatus extends Migration
{
    public function up()
    {
        $this->forge->addColumn('roles', [
            'description' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true, 'after' => 'name'],
            'status'      => ['type' => 'ENUM', 'constraint' => ['active', 'archived'], 'default' => 'active', 'after' => 'is_system'],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('roles', ['description', 'status']);
    }
}
