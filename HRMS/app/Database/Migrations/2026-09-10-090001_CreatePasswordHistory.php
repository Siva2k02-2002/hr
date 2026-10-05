<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/** Holds the last several password hashes per user so change/reset can reject reuse. */
class CreatePasswordHistory extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'             => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'user_id'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'password_hash'  => ['type' => 'VARCHAR', 'constraint' => 255],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['user_id', 'created_at']);
        $this->forge->addForeignKey('user_id', 'users', 'id', '', 'CASCADE');
        $this->forge->createTable('password_history');
    }

    public function down()
    {
        $this->forge->dropTable('password_history');
    }
}
