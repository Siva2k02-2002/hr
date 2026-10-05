<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSubscriptionHistory extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'              => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'subscription_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'action'          => ['type' => 'VARCHAR', 'constraint' => 50],
            'old_values'      => ['type' => 'TEXT', 'null' => true],
            'new_values'      => ['type' => 'TEXT', 'null' => true],
            'performed_by'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'performed_at'    => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('subscription_id');
        $this->forge->addForeignKey('subscription_id', 'subscriptions', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('performed_by', 'platform_users', 'id', '', 'SET NULL');
        $this->forge->createTable('subscription_history');
    }

    public function down()
    {
        $this->forge->dropTable('subscription_history');
    }
}
