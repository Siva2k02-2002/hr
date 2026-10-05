<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/** Foundation only — no payroll calculation happens here (Phase 5 scope). Populated by AttendanceSummaryService. */
class CreateAttendanceOvertime extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'employee_id'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'attendance_date'   => ['type' => 'DATE'],
            'shift_minutes'     => ['type' => 'INT', 'constraint' => 6, 'unsigned' => true, 'default' => 0],
            'worked_minutes'    => ['type' => 'INT', 'constraint' => 6, 'unsigned' => true, 'default' => 0],
            'overtime_minutes'  => ['type' => 'INT', 'constraint' => 6, 'unsigned' => true, 'default' => 0],
            'status'            => ['type' => 'ENUM', 'constraint' => ['pending', 'approved', 'rejected'], 'default' => 'pending'],
            'created_at'        => ['type' => 'DATETIME', 'null' => true],
            'updated_at'        => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['employee_id', 'attendance_date']);
        $this->forge->addForeignKey('employee_id', 'employees', 'id', '', 'CASCADE');
        $this->forge->createTable('attendance_overtime');
    }

    public function down()
    {
        $this->forge->dropTable('attendance_overtime');
    }
}
