<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/** Foundation only — amount_placeholder is explicitly not payroll-computed. No salary math here. */
class CreateLeaveEncashments extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'employee_id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'leave_type_id'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'financial_year'      => ['type' => 'INT', 'constraint' => 4],
            'days_encashed'       => ['type' => 'DECIMAL', 'constraint' => '5,1'],
            'amount_placeholder'  => ['type' => 'DECIMAL', 'constraint' => '10,2', 'null' => true],
            'status'              => ['type' => 'ENUM', 'constraint' => ['pending', 'approved', 'rejected', 'paid'], 'default' => 'pending'],
            'requested_at'        => ['type' => 'DATETIME', 'null' => true],
            'approved_by'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'approved_at'         => ['type' => 'DATETIME', 'null' => true],
            'remarks'             => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'created_by'          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'created_at'          => ['type' => 'DATETIME', 'null' => true],
            'updated_at'          => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'          => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('employee_id');
        $this->forge->addKey('leave_type_id');
        $this->forge->addKey('financial_year');
        $this->forge->addKey('status');
        $this->forge->addForeignKey('employee_id', 'employees', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('leave_type_id', 'leave_types', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('approved_by', 'users', 'id', '', 'SET NULL');
        $this->forge->addForeignKey('created_by', 'users', 'id', '', 'SET NULL');
        $this->forge->createTable('leave_encashments');
    }

    public function down()
    {
        $this->forge->dropTable('leave_encashments');
    }
}
