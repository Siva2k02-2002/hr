<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/** Single-row table. Master esi_enabled toggle lives on payroll_settings. */
class CreatePayrollEsiSettings extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'employee_percentage'  => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0.75],
            'employer_percentage'  => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 3.25],
            'wage_ceiling'         => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 21000.00],
            'created_at'           => ['type' => 'DATETIME', 'null' => true],
            'updated_at'           => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('payroll_esi_settings');
    }

    public function down()
    {
        $this->forge->dropTable('payroll_esi_settings');
    }
}
