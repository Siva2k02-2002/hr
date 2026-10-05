<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePayrollAdvances extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'employee_id'          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'amount'               => ['type' => 'DECIMAL', 'constraint' => '12,2'],
            'advance_date'         => ['type' => 'DATE'],
            'recovery_type'        => ['type' => 'ENUM', 'constraint' => ['lump_sum', 'installments'], 'default' => 'installments'],
            'installments_count'   => ['type' => 'SMALLINT', 'constraint' => 3, 'unsigned' => true, 'default' => 1],
            'installment_amount'   => ['type' => 'DECIMAL', 'constraint' => '10,2'],
            'recovered_amount'     => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'remaining_balance'    => ['type' => 'DECIMAL', 'constraint' => '12,2'],
            'status'               => ['type' => 'ENUM', 'constraint' => ['active', 'closed'], 'default' => 'active'],
            'reason'               => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'created_by'           => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'updated_by'           => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'created_at'           => ['type' => 'DATETIME', 'null' => true],
            'updated_at'           => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'           => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['employee_id', 'status']);
        $this->forge->addForeignKey('employee_id', 'employees', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('created_by', 'users', 'id', '', 'SET NULL');
        $this->forge->addForeignKey('updated_by', 'users', 'id', '', 'SET NULL');
        $this->forge->createTable('payroll_advances');
    }

    public function down()
    {
        $this->forge->dropTable('payroll_advances');
    }
}
