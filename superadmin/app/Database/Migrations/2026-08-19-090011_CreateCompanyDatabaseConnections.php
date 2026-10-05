<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCompanyDatabaseConnections extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'company_id'          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'db_host'             => ['type' => 'VARCHAR', 'constraint' => 150],
            'db_port'             => ['type' => 'INT', 'constraint' => 5, 'unsigned' => true, 'default' => 3306],
            'db_name'             => ['type' => 'VARCHAR', 'constraint' => 100],
            'db_username'         => ['type' => 'VARCHAR', 'constraint' => 100],
            // Encrypted at rest via CodeIgniter's Encryption service; decrypted only
            // in-memory when the tenant resolver builds the runtime DB connection group.
            'db_password_enc'     => ['type' => 'TEXT'],
            'status'              => ['type' => 'ENUM', 'constraint' => ['pending', 'provisioned', 'error'], 'default' => 'pending'],
            'last_checked_at'     => ['type' => 'DATETIME', 'null' => true],
            'created_at'          => ['type' => 'DATETIME', 'null' => true],
            'updated_at'          => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('company_id');
        $this->forge->addForeignKey('company_id', 'companies', 'id', '', 'CASCADE');
        $this->forge->createTable('company_database_connections');
    }

    public function down()
    {
        $this->forge->dropTable('company_database_connections');
    }
}
