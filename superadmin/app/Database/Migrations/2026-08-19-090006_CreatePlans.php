<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePlans extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'code'              => ['type' => 'VARCHAR', 'constraint' => 50],
            'name'              => ['type' => 'VARCHAR', 'constraint' => 100],
            'employee_limit'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 0],
            'branch_limit'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 0],
            'storage_limit_mb'  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 0],
            'duration_days'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 365],
            'is_active'         => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at'        => ['type' => 'DATETIME', 'null' => true],
            'updated_at'        => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('code');
        $this->forge->createTable('plans');
    }

    public function down()
    {
        $this->forge->dropTable('plans');
    }
}
