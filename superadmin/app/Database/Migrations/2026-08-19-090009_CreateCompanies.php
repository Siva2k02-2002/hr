<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCompanies extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'code'                    => ['type' => 'VARCHAR', 'constraint' => 50],
            'name'                    => ['type' => 'VARCHAR', 'constraint' => 150],
            'status'                  => ['type' => 'ENUM', 'constraint' => ['trial', 'active', 'expiring', 'expired', 'suspended', 'cancelled'], 'default' => 'trial'],
            'plan_id'                 => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            // No FK to subscriptions.id on purpose: subscriptions.company_id already points back
            // here, and adding a second FK the other way would be a circular dependency between
            // the two tables. The pointer is resolved in application code (SubscriptionService).
            'current_subscription_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'employee_limit'          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 0],
            'timezone'                => ['type' => 'VARCHAR', 'constraint' => 64, 'default' => 'Asia/Kolkata'],
            'currency'                => ['type' => 'VARCHAR', 'constraint' => 10, 'default' => 'INR'],
            'contact_name'            => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'contact_email'           => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'contact_phone'           => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
            'country'                 => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'trial_ends_at'           => ['type' => 'DATETIME', 'null' => true],
            'created_at'              => ['type' => 'DATETIME', 'null' => true],
            'updated_at'              => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'              => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('code');
        $this->forge->addKey('status');
        $this->forge->addForeignKey('plan_id', 'plans', 'id', '', 'RESTRICT');
        $this->forge->createTable('companies');
    }

    public function down()
    {
        $this->forge->dropTable('companies');
    }
}
