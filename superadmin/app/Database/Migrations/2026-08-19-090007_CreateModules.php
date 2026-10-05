<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateModules extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'module_key'  => ['type' => 'VARCHAR', 'constraint' => 50],
            'name'        => ['type' => 'VARCHAR', 'constraint' => 100],
            'description' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'is_core'     => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('module_key');
        $this->forge->createTable('modules');
    }

    public function down()
    {
        $this->forge->dropTable('modules');
    }
}
