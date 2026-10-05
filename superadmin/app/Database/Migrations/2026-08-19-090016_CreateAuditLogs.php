<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAuditLogs extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'platform_user_id'  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'company_id'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'action'            => ['type' => 'VARCHAR', 'constraint' => 100],
            'module'            => ['type' => 'VARCHAR', 'constraint' => 100],
            'record_type'       => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'record_id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'old_values'        => ['type' => 'TEXT', 'null' => true],
            'new_values'        => ['type' => 'TEXT', 'null' => true],
            'ip_address'        => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true],
            'user_agent'        => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at'        => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('platform_user_id');
        $this->forge->addKey('company_id');
        $this->forge->addKey('module');
        $this->forge->addKey('created_at');
        $this->forge->addForeignKey('platform_user_id', 'platform_users', 'id', '', 'SET NULL');
        $this->forge->addForeignKey('company_id', 'companies', 'id', '', 'SET NULL');
        $this->forge->createTable('audit_logs');
    }

    public function down()
    {
        $this->forge->dropTable('audit_logs');
    }
}
