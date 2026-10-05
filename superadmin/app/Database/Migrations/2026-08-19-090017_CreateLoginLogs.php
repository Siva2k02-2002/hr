<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateLoginLogs extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'               => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'platform_user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'email'            => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'status'           => ['type' => 'ENUM', 'constraint' => ['success', 'failed']],
            'ip_address'       => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true],
            'user_agent'       => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at'       => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('platform_user_id');
        $this->forge->addKey('created_at');
        $this->forge->addForeignKey('platform_user_id', 'platform_users', 'id', '', 'SET NULL');
        $this->forge->createTable('login_logs');
    }

    public function down()
    {
        $this->forge->dropTable('login_logs');
    }
}
