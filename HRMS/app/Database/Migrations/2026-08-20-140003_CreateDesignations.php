<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDesignations extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'            => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'name'          => ['type' => 'VARCHAR', 'constraint' => 150],
            'department_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'level'         => ['type' => 'INT', 'constraint' => 5, 'default' => 0],
            'status'        => ['type' => 'ENUM', 'constraint' => ['active', 'inactive'], 'default' => 'active'],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('department_id');
        $this->forge->addForeignKey('department_id', 'departments', 'id', '', 'RESTRICT');
        $this->forge->createTable('designations');
    }

    public function down()
    {
        $this->forge->dropTable('designations');
    }
}
