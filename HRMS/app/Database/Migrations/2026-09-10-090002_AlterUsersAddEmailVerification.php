<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterUsersAddEmailVerification extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'email_verified_at'             => ['type' => 'DATETIME', 'null' => true, 'after' => 'email'],
            'email_verification_token_hash' => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true, 'after' => 'email_verified_at'],
            'email_verification_expires_at' => ['type' => 'DATETIME', 'null' => true, 'after' => 'email_verification_token_hash'],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', ['email_verified_at', 'email_verification_token_hash', 'email_verification_expires_at']);
    }
}
