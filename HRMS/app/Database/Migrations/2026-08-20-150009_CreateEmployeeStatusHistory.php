<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/** Append-only lifecycle ledger — see EmployeeStatusService, the only writer. */
class CreateEmployeeStatusHistory extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'employee_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'event_type'  => [
                'type'       => 'ENUM',
                'constraint' => [
                    'joined', 'confirmed', 'promoted', 'transferred', 'department_changed', 'manager_changed',
                    'on_notice', 'relieved', 'terminated', 'rejoined', 'suspended', 'activated',
                ],
            ],
            'from_value'  => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'to_value'    => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'remarks'     => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'changed_by'  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('employee_id');
        $this->forge->addForeignKey('employee_id', 'employees', 'id', '', 'CASCADE');
        $this->forge->createTable('employee_status_history');
    }

    public function down()
    {
        $this->forge->dropTable('employee_status_history');
    }
}
