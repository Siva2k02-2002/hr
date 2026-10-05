<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Foundation only — no income tax declaration module yet (explicitly out of
 * scope for this phase). default_percentage is a flat placeholder rate
 * applied above applicable_above_gross; HR can override the computed amount
 * per employee per run via payroll_adjustments before approval.
 */
class CreatePayrollTdsSettings extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'default_percentage'      => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0],
            'applicable_above_gross'  => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0],
            'created_at'              => ['type' => 'DATETIME', 'null' => true],
            'updated_at'              => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('payroll_tds_settings');
    }

    public function down()
    {
        $this->forge->dropTable('payroll_tds_settings');
    }
}
