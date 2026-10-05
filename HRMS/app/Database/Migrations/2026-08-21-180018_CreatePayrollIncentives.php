<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePayrollIncentives extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'employee_id'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'incentive_type'    => ['type' => 'VARCHAR', 'constraint' => 100],
            'amount'            => ['type' => 'DECIMAL', 'constraint' => '10,2'],
            'payroll_month_id'  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'remarks'           => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'status'            => ['type' => 'ENUM', 'constraint' => ['pending', 'paid'], 'default' => 'pending'],
            'created_by'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'updated_by'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'created_at'        => ['type' => 'DATETIME', 'null' => true],
            'updated_at'        => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['employee_id', 'payroll_month_id']);
        $this->forge->addForeignKey('employee_id', 'employees', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('payroll_month_id', 'payroll_months', 'id', '', 'SET NULL');
        $this->forge->addForeignKey('created_by', 'users', 'id', '', 'SET NULL');
        $this->forge->addForeignKey('updated_by', 'users', 'id', '', 'SET NULL');
        $this->forge->createTable('payroll_incentives');
    }

    public function down()
    {
        $this->forge->dropTable('payroll_incentives');
    }
}
