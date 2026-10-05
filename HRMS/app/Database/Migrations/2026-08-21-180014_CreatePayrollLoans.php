<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePayrollLoans extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'employee_id'           => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'loan_number'           => ['type' => 'VARCHAR', 'constraint' => 30],
            'loan_type'             => ['type' => 'VARCHAR', 'constraint' => 100],
            'principal_amount'      => ['type' => 'DECIMAL', 'constraint' => '12,2'],
            'interest_rate'         => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0],
            'tenure_months'         => ['type' => 'SMALLINT', 'constraint' => 3, 'unsigned' => true],
            'emi_amount'            => ['type' => 'DECIMAL', 'constraint' => '10,2'],
            'start_month'           => ['type' => 'TINYINT', 'constraint' => 2, 'unsigned' => true],
            'start_year'            => ['type' => 'SMALLINT', 'constraint' => 4, 'unsigned' => true],
            'outstanding_balance'   => ['type' => 'DECIMAL', 'constraint' => '12,2'],
            'status'                => ['type' => 'ENUM', 'constraint' => ['active', 'closed', 'foreclosed'], 'default' => 'active'],
            'created_by'            => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'updated_by'            => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'created_at'            => ['type' => 'DATETIME', 'null' => true],
            'updated_at'            => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'            => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('loan_number');
        $this->forge->addKey(['employee_id', 'status']);
        $this->forge->addForeignKey('employee_id', 'employees', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('created_by', 'users', 'id', '', 'SET NULL');
        $this->forge->addForeignKey('updated_by', 'users', 'id', '', 'SET NULL');
        $this->forge->createTable('payroll_loans');
    }

    public function down()
    {
        $this->forge->dropTable('payroll_loans');
    }
}
