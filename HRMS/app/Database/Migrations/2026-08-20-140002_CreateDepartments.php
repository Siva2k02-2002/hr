<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/** head_user_id has no FK for the same reason as branches.manager_user_id. */
class CreateDepartments extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'name'        => ['type' => 'VARCHAR', 'constraint' => 150],
            'code'        => ['type' => 'VARCHAR', 'constraint' => 30],
            'branch_id'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'head_user_id'=> ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'status'      => ['type' => 'ENUM', 'constraint' => ['active', 'inactive'], 'default' => 'active'],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('code');
        $this->forge->addKey('branch_id');
        $this->forge->addForeignKey('branch_id', 'branches', 'id', '', 'RESTRICT');
        $this->forge->createTable('departments');
    }

    public function down()
    {
        $this->forge->dropTable('departments');
    }
}
