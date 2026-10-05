<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAuditLogs extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'user_id'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'action'      => ['type' => 'VARCHAR', 'constraint' => 40],
            'module'      => ['type' => 'VARCHAR', 'constraint' => 60],
            'record_type' => ['type' => 'VARCHAR', 'constraint' => 60, 'null' => true],
            'record_id'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'old_values'  => ['type' => 'TEXT', 'null' => true],
            'new_values'  => ['type' => 'TEXT', 'null' => true],
            'ip_address'  => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true],
            'user_agent'  => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('user_id');
        $this->forge->addKey('module');
        $this->forge->createTable('audit_logs');
    }

    public function down()
    {
        $this->forge->dropTable('audit_logs');
    }
}
