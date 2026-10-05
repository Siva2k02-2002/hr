<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/** Captures the device that created each remember-me token, so the IAM page's Session Management card can show something more useful than a bare token row. */
class AlterRememberTokensAddDeviceInfo extends Migration
{
    public function up()
    {
        $this->forge->addColumn('remember_tokens', [
            'ip_address' => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true, 'after' => 'validator_hash'],
            'user_agent' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true, 'after' => 'ip_address'],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('remember_tokens', ['ip_address', 'user_agent']);
    }
}
