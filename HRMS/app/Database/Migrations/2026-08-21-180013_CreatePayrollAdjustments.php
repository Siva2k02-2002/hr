<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/** Ad-hoc pre-approval corrections to a payroll_run_items row (also how TDS gets manually overridden — see PayrollDeductionsService). */
class CreatePayrollAdjustments extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'payroll_run_item_id'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'type'                  => ['type' => 'ENUM', 'constraint' => ['earning', 'deduction']],
            'label'                 => ['type' => 'VARCHAR', 'constraint' => 150],
            'amount'                => ['type' => 'DECIMAL', 'constraint' => '10,2'],
            'reason'                => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'created_by'            => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'created_at'            => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('payroll_run_item_id');
        $this->forge->addForeignKey('payroll_run_item_id', 'payroll_run_items', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('created_by', 'users', 'id', '', 'SET NULL');
        $this->forge->createTable('payroll_adjustments');
    }

    public function down()
    {
        $this->forge->dropTable('payroll_adjustments');
    }
}
