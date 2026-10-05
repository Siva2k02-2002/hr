<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * percentage_of stores another component's `code` (e.g. HRA is 40% "of" BASIC)
 * — resolved live against the same structure's items by SalaryStructureService,
 * never a hard FK since the basis component varies per structure.
 * formula is only used when calculation_type = 'formula' and is evaluated by
 * PayrollFormulaEvaluator (whitelisted arithmetic, no eval()).
 */
class CreatePayrollSalaryComponents extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'name'              => ['type' => 'VARCHAR', 'constraint' => 100],
            'code'              => ['type' => 'VARCHAR', 'constraint' => 30],
            'type'              => ['type' => 'ENUM', 'constraint' => ['earning', 'deduction']],
            'calculation_type'  => ['type' => 'ENUM', 'constraint' => ['fixed', 'percentage', 'formula'], 'default' => 'fixed'],
            'percentage_of'     => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
            'formula'           => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'is_taxable'        => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'pf_applicable'     => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'esi_applicable'    => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'display_order'     => ['type' => 'INT', 'constraint' => 5, 'default' => 0],
            'status'            => ['type' => 'ENUM', 'constraint' => ['active', 'inactive'], 'default' => 'active'],
            'created_by'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'updated_by'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'created_at'        => ['type' => 'DATETIME', 'null' => true],
            'updated_at'        => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'        => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('code');
        $this->forge->addKey('type');
        $this->forge->addKey('status');
        $this->forge->addForeignKey('created_by', 'users', 'id', '', 'SET NULL');
        $this->forge->addForeignKey('updated_by', 'users', 'id', '', 'SET NULL');
        $this->forge->createTable('payroll_salary_components');
    }

    public function down()
    {
        $this->forge->dropTable('payroll_salary_components');
    }
}
