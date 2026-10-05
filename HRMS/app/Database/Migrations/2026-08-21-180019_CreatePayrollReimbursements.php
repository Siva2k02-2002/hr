<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePayrollReimbursements extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'employee_id'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'expense_type'      => ['type' => 'VARCHAR', 'constraint' => 100],
            'amount'            => ['type' => 'DECIMAL', 'constraint' => '10,2'],
            'expense_date'      => ['type' => 'DATE'],
            'attachment_path'   => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'status'            => ['type' => 'ENUM', 'constraint' => ['pending', 'approved', 'rejected', 'paid'], 'default' => 'pending'],
            'approved_by'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'approved_at'       => ['type' => 'DATETIME', 'null' => true],
            'payroll_month_id'  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'remarks'           => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'created_by'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'updated_by'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'created_at'        => ['type' => 'DATETIME', 'null' => true],
            'updated_at'        => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'        => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['employee_id', 'status']);
        $this->forge->addKey('payroll_month_id');
        $this->forge->addForeignKey('employee_id', 'employees', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('approved_by', 'users', 'id', '', 'SET NULL');
        $this->forge->addForeignKey('payroll_month_id', 'payroll_months', 'id', '', 'SET NULL');
        $this->forge->addForeignKey('created_by', 'users', 'id', '', 'SET NULL');
        $this->forge->addForeignKey('updated_by', 'users', 'id', '', 'SET NULL');
        $this->forge->createTable('payroll_reimbursements');
    }

    public function down()
    {
        $this->forge->dropTable('payroll_reimbursements');
    }
}
