<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/** One optional delegation row per leave_application — unique key enforces the 1:1. */
class CreateLeaveDelegations extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'leave_application_id'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'delegate_employee_id'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'from_date'              => ['type' => 'DATE'],
            'to_date'                => ['type' => 'DATE'],
            'notes'                  => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'notified_at'            => ['type' => 'DATETIME', 'null' => true],
            'created_by'             => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'created_at'             => ['type' => 'DATETIME', 'null' => true],
            'updated_at'             => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('leave_application_id');
        $this->forge->addKey('delegate_employee_id');
        $this->forge->addForeignKey('leave_application_id', 'leave_applications', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('delegate_employee_id', 'employees', 'id', '', 'RESTRICT');
        $this->forge->addForeignKey('created_by', 'users', 'id', '', 'SET NULL');
        $this->forge->createTable('leave_delegations');
    }

    public function down()
    {
        $this->forge->dropTable('leave_delegations');
    }
}
