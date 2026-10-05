<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * The payslip line. earnings_breakdown/deductions_breakdown are JSON snapshots
 * of every component computed for this run (see PayrollEarningsService /
 * PayrollDeductionsService) — this is what "server calculates everything, show
 * breakdown" persists, without a separate employee-salary-items snapshot table.
 * unique(payroll_run_id, employee_id) is the DB-level "one payroll per
 * employee/month" guard (a month has at most one non-cancelled run).
 */
class CreatePayrollRunItems extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'payroll_run_id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'payroll_month_id'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'employee_id'            => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'salary_structure_id'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'settlement_type'        => ['type' => 'ENUM', 'constraint' => ['regular', 'final'], 'default' => 'regular'],
            'working_days'           => ['type' => 'DECIMAL', 'constraint' => '4,1', 'default' => 0],
            'present_days'           => ['type' => 'DECIMAL', 'constraint' => '4,1', 'default' => 0],
            'paid_leave_days'        => ['type' => 'DECIMAL', 'constraint' => '4,1', 'default' => 0],
            'lop_days'               => ['type' => 'DECIMAL', 'constraint' => '4,1', 'default' => 0],
            'half_days'              => ['type' => 'DECIMAL', 'constraint' => '4,1', 'default' => 0],
            'overtime_hours'         => ['type' => 'DECIMAL', 'constraint' => '6,2', 'default' => 0],
            'overtime_amount'        => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'gross_earnings'         => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'gross_deductions'       => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'net_salary'             => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'pf_employee'            => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0],
            'pf_employer'            => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0],
            'esi_employee'           => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0],
            'esi_employer'           => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0],
            'professional_tax'       => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0],
            'tds'                    => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0],
            'loan_deduction'         => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0],
            'advance_deduction'      => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0],
            'bonus_amount'           => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0],
            'incentive_amount'       => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0],
            'reimbursement_amount'   => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0],
            'arrears_amount'         => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0],
            'earnings_breakdown'     => ['type' => 'TEXT', 'null' => true],
            'deductions_breakdown'   => ['type' => 'TEXT', 'null' => true],
            'status'                 => ['type' => 'ENUM', 'constraint' => ['draft', 'approved', 'locked', 'paid', 'hold'], 'default' => 'draft'],
            'remarks'                => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'created_by'             => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'updated_by'             => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'created_at'             => ['type' => 'DATETIME', 'null' => true],
            'updated_at'             => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['payroll_run_id', 'employee_id']);
        $this->forge->addKey(['employee_id', 'status']);
        $this->forge->addKey('payroll_month_id');
        $this->forge->addForeignKey('payroll_run_id', 'payroll_runs', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('payroll_month_id', 'payroll_months', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('employee_id', 'employees', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('salary_structure_id', 'payroll_salary_structures', 'id', '', 'SET NULL');
        $this->forge->addForeignKey('created_by', 'users', 'id', '', 'SET NULL');
        $this->forge->addForeignKey('updated_by', 'users', 'id', '', 'SET NULL');
        $this->forge->createTable('payroll_run_items');
    }

    public function down()
    {
        $this->forge->dropTable('payroll_run_items');
    }
}
