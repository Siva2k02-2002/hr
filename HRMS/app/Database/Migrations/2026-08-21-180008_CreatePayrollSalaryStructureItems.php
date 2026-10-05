<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/** The rows of the Salary Structure Builder UI. value is either a fixed amount or a percentage number depending on calculation_type. */
class CreatePayrollSalaryStructureItems extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'salary_structure_id'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'salary_component_id'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'calculation_type'      => ['type' => 'ENUM', 'constraint' => ['fixed', 'percentage', 'formula'], 'default' => 'fixed'],
            'value'                 => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'formula'               => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'is_editable'           => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'display_order'         => ['type' => 'INT', 'constraint' => 5, 'default' => 0],
            'created_at'            => ['type' => 'DATETIME', 'null' => true],
            'updated_at'            => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['salary_structure_id', 'salary_component_id']);
        $this->forge->addKey('salary_component_id');
        $this->forge->addForeignKey('salary_structure_id', 'payroll_salary_structures', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('salary_component_id', 'payroll_salary_components', 'id', '', 'RESTRICT');
        $this->forge->createTable('payroll_salary_structure_items');
    }

    public function down()
    {
        $this->forge->dropTable('payroll_salary_structure_items');
    }
}
