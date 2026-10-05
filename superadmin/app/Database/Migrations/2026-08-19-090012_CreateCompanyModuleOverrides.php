<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCompanyModuleOverrides extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'company_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'module_id'  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'is_enabled' => ['type' => 'TINYINT', 'constraint' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey(['company_id', 'module_id']);
        $this->forge->addForeignKey('company_id', 'companies', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('module_id', 'modules', 'id', '', 'CASCADE');
        $this->forge->createTable('company_module_overrides');
    }

    public function down()
    {
        $this->forge->dropTable('company_module_overrides');
    }
}
