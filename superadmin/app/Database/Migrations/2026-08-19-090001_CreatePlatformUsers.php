<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePlatformUsers extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'name'                  => ['type' => 'VARCHAR', 'constraint' => 150],
            'email'                 => ['type' => 'VARCHAR', 'constraint' => 150],
            'password_hash'         => ['type' => 'VARCHAR', 'constraint' => 255],
            'status'                => ['type' => 'ENUM', 'constraint' => ['active', 'inactive'], 'default' => 'active'],
            'must_change_password'  => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'last_login_at'         => ['type' => 'DATETIME', 'null' => true],
            'created_at'            => ['type' => 'DATETIME', 'null' => true],
            'updated_at'            => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('email');
        $this->forge->createTable('platform_users');
    }

    public function down()
    {
        $this->forge->dropTable('platform_users');
    }
}
