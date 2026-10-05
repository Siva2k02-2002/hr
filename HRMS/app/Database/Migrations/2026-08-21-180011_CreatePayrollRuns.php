<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/** One generation batch for a payroll_month. Only one non-cancelled run per month is allowed — enforced in PayrollRunService, not the schema, so a cancelled+regenerated run stays auditable. */
class CreatePayrollRuns extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'payroll_month_id'  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'run_type'          => ['type' => 'ENUM', 'constraint' => ['regular', 'off_cycle'], 'default' => 'regular'],
            'status'            => ['type' => 'ENUM', 'constraint' => ['draft', 'generated', 'approved', 'locked', 'paid', 'cancelled'], 'default' => 'draft'],
            'total_employees'   => ['type' => 'INT', 'constraint' => 6, 'unsigned' => true, 'default' => 0],
            'total_gross'       => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => 0],
            'total_net'         => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => 0],
            'total_pf'          => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => 0],
            'total_esi'         => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => 0],
            'total_pt'          => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => 0],
            'total_tds'         => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => 0],
            'generated_by'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'generated_at'      => ['type' => 'DATETIME', 'null' => true],
            'approved_by'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'approved_at'       => ['type' => 'DATETIME', 'null' => true],
            'locked_by'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'locked_at'         => ['type' => 'DATETIME', 'null' => true],
            'paid_by'           => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'paid_at'           => ['type' => 'DATETIME', 'null' => true],
            'created_at'        => ['type' => 'DATETIME', 'null' => true],
            'updated_at'        => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['payroll_month_id', 'status']);
        $this->forge->addForeignKey('payroll_month_id', 'payroll_months', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('generated_by', 'users', 'id', '', 'SET NULL');
        $this->forge->addForeignKey('approved_by', 'users', 'id', '', 'SET NULL');
        $this->forge->addForeignKey('locked_by', 'users', 'id', '', 'SET NULL');
        $this->forge->addForeignKey('paid_by', 'users', 'id', '', 'SET NULL');
        $this->forge->createTable('payroll_runs');
    }

    public function down()
    {
        $this->forge->dropTable('payroll_runs');
    }
}
