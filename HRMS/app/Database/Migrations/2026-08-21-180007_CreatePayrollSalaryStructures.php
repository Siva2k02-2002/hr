<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePayrollSalaryStructures extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'              => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'name'            => ['type' => 'VARCHAR', 'constraint' => 150],
            'description'     => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'effective_from'  => ['type' => 'DATE'],
            'status'          => ['type' => 'ENUM', 'constraint' => ['active', 'inactive'], 'default' => 'active'],
            'created_by'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'updated_by'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
            'updated_at'      => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('name');
        $this->forge->addKey('status');
        $this->forge->addForeignKey('created_by', 'users', 'id', '', 'SET NULL');
        $this->forge->addForeignKey('updated_by', 'users', 'id', '', 'SET NULL');
        $this->forge->createTable('payroll_salary_structures');
    }

    public function down()
    {
        $this->forge->dropTable('payroll_salary_structures');
    }
}
