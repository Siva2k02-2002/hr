<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/** Append-only run history for LeaveCarryForwardService — one row per employee+type per run. */
class CreateLeaveCarryForwardHistory extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'employee_id'               => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'leave_type_id'             => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'from_financial_year'       => ['type' => 'INT', 'constraint' => 4],
            'to_financial_year'         => ['type' => 'INT', 'constraint' => 4],
            'eligible_balance'          => ['type' => 'DECIMAL', 'constraint' => '6,2'],
            'carried_forward_days'      => ['type' => 'DECIMAL', 'constraint' => '6,2'],
            'expired_days'              => ['type' => 'DECIMAL', 'constraint' => '6,2', 'default' => 0],
            'carry_forward_rule_limit'  => ['type' => 'DECIMAL', 'constraint' => '5,1', 'null' => true],
            'expiry_date'               => ['type' => 'DATE', 'null' => true],
            'processed_by'              => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'run_batch_id'              => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
            'created_at'                => ['type' => 'DATETIME'],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('employee_id');
        $this->forge->addKey('leave_type_id');
        $this->forge->addKey(['from_financial_year', 'to_financial_year']);
        $this->forge->addKey('run_batch_id');
        $this->forge->addForeignKey('employee_id', 'employees', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('leave_type_id', 'leave_types', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('processed_by', 'users', 'id', '', 'SET NULL');
        $this->forge->createTable('leave_carry_forward_history');
    }

    public function down()
    {
        $this->forge->dropTable('leave_carry_forward_history');
    }
}
