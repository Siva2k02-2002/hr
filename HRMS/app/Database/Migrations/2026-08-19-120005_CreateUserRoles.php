<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUserRoles extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'user_id'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'role_id'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey(['user_id', 'role_id']);
        $this->forge->addForeignKey('user_id', 'users', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('role_id', 'roles', 'id', '', 'CASCADE');
        $this->forge->createTable('user_roles');
    }

    public function down()
    {
        $this->forge->dropTable('user_roles');
    }
}
