<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/** Payslip PDF layout: 'standard' (full A4) or 'half' (compact, A4 half-page). */
class AddPayslipFormatToPayrollSettings extends Migration
{
    public function up()
    {
        $this->forge->addColumn('payroll_settings', [
            'payslip_format' => ['type' => 'ENUM', 'constraint' => ['standard', 'half'], 'default' => 'standard', 'after' => 'payslip_prefix'],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('payroll_settings', 'payslip_format');
    }
}
