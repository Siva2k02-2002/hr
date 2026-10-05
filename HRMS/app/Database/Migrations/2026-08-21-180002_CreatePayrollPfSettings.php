<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/** Single-row table. Master pf_enabled toggle lives on payroll_settings — this table only holds the numeric configuration. */
class CreatePayrollPfSettings extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'employee_percentage'  => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 12.00],
            'employer_percentage'  => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 12.00],
            'wage_ceiling'         => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 15000.00],
            'pf_wage_basis'        => ['type' => 'ENUM', 'constraint' => ['basic', 'basic_da', 'gross'], 'default' => 'basic'],
            'created_at'           => ['type' => 'DATETIME', 'null' => true],
            'updated_at'           => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('payroll_pf_settings');
    }

    public function down()
    {
        $this->forge->dropTable('payroll_pf_settings');
    }
}
