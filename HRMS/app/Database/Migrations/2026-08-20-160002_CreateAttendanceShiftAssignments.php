<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * One row per employee per assignment period — "department/branch/bulk
 * assignment" in the UI are just bulk-insert conveniences over this same
 * table (see AttendanceShiftAssignmentService), not separate storage.
 * effective_to is closed off (not deleted) when a new assignment starts,
 * which is what gives history + future-dating for free.
 */
class CreateAttendanceShiftAssignments extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'              => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'employee_id'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'shift_id'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'effective_from'  => ['type' => 'DATE'],
            'effective_to'    => ['type' => 'DATE', 'null' => true],
            'created_by'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['employee_id', 'effective_from']);
        $this->forge->addForeignKey('employee_id', 'employees', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('shift_id', 'attendance_shifts', 'id', '', 'RESTRICT');
        $this->forge->createTable('attendance_shift_assignments');
    }

    public function down()
    {
        $this->forge->dropTable('attendance_shift_assignments');
    }
}
