<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePlanModules extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'plan_id'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'module_id'  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey(['plan_id', 'module_id']);
        $this->forge->addForeignKey('plan_id', 'plans', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('module_id', 'modules', 'id', '', 'CASCADE');
        $this->forge->createTable('plan_modules');
    }

    public function down()
    {
        $this->forge->dropTable('plan_modules');
    }
}
