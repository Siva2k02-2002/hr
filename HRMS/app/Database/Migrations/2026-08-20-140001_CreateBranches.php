<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * manager_user_id deliberately has NO foreign key: branches is created
 * before users gains its org-structure columns in this same migration
 * batch, and a branch manager is optional. Resolved/validated at the
 * application layer (BranchService), matching the pattern already used
 * for companies.current_subscription_id in the platform schema.
 */
class CreateBranches extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'              => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'name'            => ['type' => 'VARCHAR', 'constraint' => 150],
            'code'            => ['type' => 'VARCHAR', 'constraint' => 30],
            'address'         => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'phone'           => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
            'email'           => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'manager_user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'status'          => ['type' => 'ENUM', 'constraint' => ['active', 'inactive'], 'default' => 'active'],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
            'updated_at'      => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('code');
        $this->forge->createTable('branches');
    }

    public function down()
    {
        $this->forge->dropTable('branches');
    }
}
