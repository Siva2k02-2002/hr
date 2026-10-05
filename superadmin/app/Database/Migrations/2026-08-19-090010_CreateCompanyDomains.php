<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCompanyDomains extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'company_id'  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'domain'      => ['type' => 'VARCHAR', 'constraint' => 191],
            'is_primary'  => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'verified_at' => ['type' => 'DATETIME', 'null' => true],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('domain');
        $this->forge->addKey('company_id');
        $this->forge->addForeignKey('company_id', 'companies', 'id', '', 'CASCADE');
        $this->forge->createTable('company_domains');
    }

    public function down()
    {
        $this->forge->dropTable('company_domains');
    }
}
