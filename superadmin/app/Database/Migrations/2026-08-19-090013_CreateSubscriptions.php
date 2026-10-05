<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSubscriptions extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'company_id'             => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'plan_id'                => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'starts_at'              => ['type' => 'DATE'],
            'expires_at'             => ['type' => 'DATE'],
            'employee_limit_override'=> ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'status'                 => ['type' => 'ENUM', 'constraint' => ['trial', 'active', 'expiring', 'expired', 'suspended', 'cancelled'], 'default' => 'trial'],
            'remarks'                => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_by'             => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'created_at'             => ['type' => 'DATETIME', 'null' => true],
            'updated_at'             => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('company_id');
        $this->forge->addKey('status');
        $this->forge->addForeignKey('company_id', 'companies', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('plan_id', 'plans', 'id', '', 'RESTRICT');
        $this->forge->addForeignKey('created_by', 'platform_users', 'id', '', 'SET NULL');
        $this->forge->createTable('subscriptions');
    }

    public function down()
    {
        $this->forge->dropTable('subscriptions');
    }
}
