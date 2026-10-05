<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/** Single-row table, same pattern as attendance_settings/leave_settings — see PayrollSettingsService::current(). */
class CreatePayrollSettings extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'payroll_start_day'           => ['type' => 'TINYINT', 'constraint' => 2, 'unsigned' => true, 'default' => 1],
            'payroll_end_day'             => ['type' => 'TINYINT', 'constraint' => 2, 'unsigned' => true, 'default' => 31],
            'salary_payment_day'          => ['type' => 'TINYINT', 'constraint' => 2, 'unsigned' => true, 'default' => 7],
            'financial_year_start_month'  => ['type' => 'TINYINT', 'constraint' => 2, 'unsigned' => true, 'default' => 4],
            'currency'                    => ['type' => 'VARCHAR', 'constraint' => 3, 'default' => 'INR'],
            'working_days_basis'         => ['type' => 'ENUM', 'constraint' => ['calendar', 'fixed'], 'default' => 'calendar'],
            'fixed_working_days'          => ['type' => 'TINYINT', 'constraint' => 2, 'unsigned' => true, 'default' => 30],
            'overtime_enabled'            => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'overtime_multiplier'        => ['type' => 'DECIMAL', 'constraint' => '3,2', 'default' => 1.50],
            'overtime_rate_basis'        => ['type' => 'ENUM', 'constraint' => ['basic', 'gross'], 'default' => 'basic'],
            'lop_enabled'                 => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'lop_deduction_basis'        => ['type' => 'ENUM', 'constraint' => ['basic', 'gross'], 'default' => 'gross'],
            'pf_enabled'                  => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'esi_enabled'                 => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'pt_enabled'                  => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'tds_enabled'                 => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'payslip_prefix'              => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'PAY'],
            'lock_after_approval'        => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at'                  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'                  => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('payroll_settings');
    }

    public function down()
    {
        $this->forge->dropTable('payroll_settings');
    }
}
