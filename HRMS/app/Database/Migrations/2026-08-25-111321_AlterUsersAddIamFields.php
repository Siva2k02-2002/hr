<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/** Additive columns only, for the Employee Login Account → IAM page (Phase 7.1). No existing column is touched. */
class AlterUsersAddIamFields extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'login_count'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 0, 'after' => 'last_login_at'],
            'locked_reason'      => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true, 'after' => 'locked_until'],
            'allow_remember_me'  => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1, 'after' => 'session_version'],
            'allow_mobile_login' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1, 'after' => 'allow_remember_me'],
            'allow_web_login'    => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1, 'after' => 'allow_mobile_login'],
            'require_2fa'        => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0, 'after' => 'allow_web_login'],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', [
            'login_count', 'locked_reason', 'allow_remember_me', 'allow_mobile_login', 'allow_web_login', 'require_2fa',
        ]);
    }
}
