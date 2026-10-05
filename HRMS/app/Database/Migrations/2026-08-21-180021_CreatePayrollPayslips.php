<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/** Metadata + download audit only — the PDF itself is rendered on demand by PayslipService (dompdf), never stored on disk. */
class CreatePayrollPayslips extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'payroll_run_item_id'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'employee_id'           => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'payroll_month_id'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'payslip_number'        => ['type' => 'VARCHAR', 'constraint' => 50],
            'generated_at'          => ['type' => 'DATETIME', 'null' => true],
            'downloaded_at'         => ['type' => 'DATETIME', 'null' => true],
            'downloaded_count'      => ['type' => 'INT', 'constraint' => 6, 'unsigned' => true, 'default' => 0],
            'created_by'            => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'created_at'            => ['type' => 'DATETIME', 'null' => true],
            'updated_at'            => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('payroll_run_item_id');
        $this->forge->addUniqueKey('payslip_number');
        $this->forge->addKey('employee_id');
        $this->forge->addKey('payroll_month_id');
        $this->forge->addForeignKey('payroll_run_item_id', 'payroll_run_items', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('employee_id', 'employees', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('payroll_month_id', 'payroll_months', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('created_by', 'users', 'id', '', 'SET NULL');
        $this->forge->createTable('payroll_payslips');
    }

    public function down()
    {
        $this->forge->dropTable('payroll_payslips');
    }
}
