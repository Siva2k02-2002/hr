<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/** Assignment history: a new assignment inserts a new row and closes the previous one's effective_to — never overwritten. effective_from in the future is 'scheduled' until its date arrives. */
class CreatePayrollEmployeeSalary extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'employee_id'          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'salary_structure_id'  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'effective_from'       => ['type' => 'DATE'],
            'effective_to'         => ['type' => 'DATE', 'null' => true],
            'gross_salary'         => ['type' => 'DECIMAL', 'constraint' => '12,2'],
            'ctc'                  => ['type' => 'DECIMAL', 'constraint' => '12,2'],
            'status'               => ['type' => 'ENUM', 'constraint' => ['scheduled', 'active', 'superseded'], 'default' => 'active'],
            'created_by'           => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'updated_by'           => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'created_at'           => ['type' => 'DATETIME', 'null' => true],
            'updated_at'           => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'           => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['employee_id', 'status']);
        $this->forge->addKey('effective_from');
        $this->forge->addKey('salary_structure_id');
        $this->forge->addForeignKey('employee_id', 'employees', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('salary_structure_id', 'payroll_salary_structures', 'id', '', 'RESTRICT');
        $this->forge->addForeignKey('created_by', 'users', 'id', '', 'SET NULL');
        $this->forge->addForeignKey('updated_by', 'users', 'id', '', 'SET NULL');
        $this->forge->createTable('payroll_employee_salary');
    }

    public function down()
    {
        $this->forge->dropTable('payroll_employee_salary');
    }
}
