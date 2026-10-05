<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * The balance ledger — mutated by delta arithmetic on the stored closing_balance
 * by LeaveBalanceService, never recomputed via SUM() over applications at read
 * time. One row per employee+leave_type+financial_year.
 */
class CreateEmployeeLeaveBalances extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'employee_id'          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'leave_type_id'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'leave_policy_id'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'financial_year'       => ['type' => 'INT', 'constraint' => 4],
            'opening_balance'      => ['type' => 'DECIMAL', 'constraint' => '6,2', 'default' => 0],
            'earned'               => ['type' => 'DECIMAL', 'constraint' => '6,2', 'default' => 0],
            'availed'              => ['type' => 'DECIMAL', 'constraint' => '6,2', 'default' => 0],
            'adjusted'             => ['type' => 'DECIMAL', 'constraint' => '6,2', 'default' => 0],
            'carry_forward_in'     => ['type' => 'DECIMAL', 'constraint' => '6,2', 'default' => 0],
            'carry_forward_out'    => ['type' => 'DECIMAL', 'constraint' => '6,2', 'default' => 0],
            'encashed'             => ['type' => 'DECIMAL', 'constraint' => '6,2', 'default' => 0],
            'closing_balance'      => ['type' => 'DECIMAL', 'constraint' => '6,2', 'default' => 0],
            'last_transaction_at'  => ['type' => 'DATETIME', 'null' => true],
            'created_at'           => ['type' => 'DATETIME', 'null' => true],
            'updated_at'           => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['employee_id', 'leave_type_id', 'financial_year']);
        $this->forge->addKey('leave_type_id');
        $this->forge->addKey('financial_year');
        $this->forge->addForeignKey('employee_id', 'employees', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('leave_type_id', 'leave_types', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('leave_policy_id', 'leave_policies', 'id', '', 'SET NULL');
        $this->forge->createTable('employee_leave_balances');
    }

    public function down()
    {
        $this->forge->dropTable('employee_leave_balances');
    }
}
