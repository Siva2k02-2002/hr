<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/** Auto-generated in full when a loan is created (PayrollLoanService::create()); payroll_run_item_id is stamped in once a run's approval actually deducts that installment. */
class CreatePayrollLoanInstallments extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'loan_id'               => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'installment_no'        => ['type' => 'SMALLINT', 'constraint' => 3, 'unsigned' => true],
            'due_year'              => ['type' => 'SMALLINT', 'constraint' => 4, 'unsigned' => true],
            'due_month'             => ['type' => 'TINYINT', 'constraint' => 2, 'unsigned' => true],
            'emi_amount'            => ['type' => 'DECIMAL', 'constraint' => '10,2'],
            'principal_component'   => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0],
            'interest_component'    => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0],
            'status'                => ['type' => 'ENUM', 'constraint' => ['pending', 'deducted', 'skipped'], 'default' => 'pending'],
            'payroll_run_item_id'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'deducted_at'           => ['type' => 'DATETIME', 'null' => true],
            'created_at'            => ['type' => 'DATETIME', 'null' => true],
            'updated_at'            => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['loan_id', 'installment_no']);
        $this->forge->addKey(['due_year', 'due_month', 'status']);
        $this->forge->addForeignKey('loan_id', 'payroll_loans', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('payroll_run_item_id', 'payroll_run_items', 'id', '', 'SET NULL');
        $this->forge->createTable('payroll_loan_installments');
    }

    public function down()
    {
        $this->forge->dropTable('payroll_loan_installments');
    }
}
