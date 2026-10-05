<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Standard selector/validator persistent-login pattern — the cookie holds
 * both halves, but only the validator's hash is stored, so a stolen DB
 * row alone can't forge a login. Selector is indexed for fast lookup;
 * validator is checked with hash_equals after hashing the cookie's copy.
 */
class CreateRememberTokens extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'              => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'user_id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'selector'        => ['type' => 'VARCHAR', 'constraint' => 40],
            'validator_hash'  => ['type' => 'VARCHAR', 'constraint' => 255],
            'expires_at'      => ['type' => 'DATETIME'],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('selector');
        $this->forge->addKey('user_id');
        $this->forge->addForeignKey('user_id', 'users', 'id', '', 'CASCADE');
        $this->forge->createTable('remember_tokens');
    }

    public function down()
    {
        $this->forge->dropTable('remember_tokens');
    }
}
