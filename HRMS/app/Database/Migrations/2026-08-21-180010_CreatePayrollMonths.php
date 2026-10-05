<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/** The payroll cycle definition for one calendar month. payroll_runs references this; status is kept in sync with its run by PayrollRunService/PayrollApprovalService/PayrollLockService/PayrollPayService. */
class CreatePayrollMonths extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'           => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'month'        => ['type' => 'TINYINT', 'constraint' => 2, 'unsigned' => true],
            'year'         => ['type' => 'SMALLINT', 'constraint' => 4, 'unsigned' => true],
            'start_date'   => ['type' => 'DATE'],
            'end_date'     => ['type' => 'DATE'],
            'status'       => ['type' => 'ENUM', 'constraint' => ['draft', 'generated', 'approved', 'locked', 'paid'], 'default' => 'draft'],
            'approved_by'  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'approved_at'  => ['type' => 'DATETIME', 'null' => true],
            'locked_by'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'locked_at'    => ['type' => 'DATETIME', 'null' => true],
            'paid_at'      => ['type' => 'DATETIME', 'null' => true],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
            'updated_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['month', 'year']);
        $this->forge->addKey('status');
        $this->forge->addForeignKey('approved_by', 'users', 'id', '', 'SET NULL');
        $this->forge->addForeignKey('locked_by', 'users', 'id', '', 'SET NULL');
        $this->forge->createTable('payroll_months');
    }

    public function down()
    {
        $this->forge->dropTable('payroll_months');
    }
}
