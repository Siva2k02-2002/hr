<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Step-by-step provisioning trail, safe to show directly in the Super
 * Admin UI. Never store passwords, connection strings, or raw exception
 * text here — only step names and short, sanitized messages.
 */
class CreateProvisioningLogs extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'company_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'step'       => ['type' => 'VARCHAR', 'constraint' => 60],
            'status'     => ['type' => 'ENUM', 'constraint' => ['started', 'completed', 'failed']],
            'message'    => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('company_id');
        $this->forge->addForeignKey('company_id', 'companies', 'id', '', 'CASCADE');
        $this->forge->createTable('provisioning_logs');
    }

    public function down()
    {
        $this->forge->dropTable('provisioning_logs');
    }
}
