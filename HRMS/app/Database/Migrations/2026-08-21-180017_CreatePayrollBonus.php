<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/** bonus_batch_id groups the rows created by one bulk-apply-to-employees action in the UI; duplicate-bonus prevention (same employee/month/type) is enforced in PayrollBonusService, not a DB unique key, since payroll_month_id is nullable until scheduled. */
class CreatePayrollBonus extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'bonus_batch_id'    => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
            'employee_id'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'bonus_type'        => ['type' => 'ENUM', 'constraint' => ['festival', 'annual', 'performance', 'other'], 'default' => 'other'],
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
        $this->forge->addKey('bonus_batch_id');
        $this->forge->addKey(['employee_id', 'payroll_month_id']);
        $this->forge->addForeignKey('employee_id', 'employees', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('payroll_month_id', 'payroll_months', 'id', '', 'SET NULL');
        $this->forge->addForeignKey('created_by', 'users', 'id', '', 'SET NULL');
        $this->forge->addForeignKey('updated_by', 'users', 'id', '', 'SET NULL');
        $this->forge->createTable('payroll_bonus');
    }

    public function down()
    {
        $this->forge->dropTable('payroll_bonus');
    }
}
