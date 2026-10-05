<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * One row per employee per day — the aggregate every dashboard/report/list
 * page actually queries. Recomputed from attendance_logs by
 * AttendanceSummaryService; never written to directly by the punch flow.
 */
class CreateAttendance extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'employee_id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'attendance_date'     => ['type' => 'DATE'],
            'shift_id'            => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'first_punch_in_at'   => ['type' => 'DATETIME', 'null' => true],
            'last_punch_out_at'   => ['type' => 'DATETIME', 'null' => true],
            'working_minutes'     => ['type' => 'INT', 'constraint' => 6, 'unsigned' => true, 'default' => 0],
            'break_minutes'       => ['type' => 'INT', 'constraint' => 6, 'unsigned' => true, 'default' => 0],
            'late_minutes'        => ['type' => 'INT', 'constraint' => 6, 'unsigned' => true, 'default' => 0],
            'early_exit_minutes'  => ['type' => 'INT', 'constraint' => 6, 'unsigned' => true, 'default' => 0],
            'overtime_minutes'    => ['type' => 'INT', 'constraint' => 6, 'unsigned' => true, 'default' => 0],
            'status'              => [
                'type'       => 'ENUM',
                'constraint' => ['present', 'absent', 'half_day', 'holiday', 'weekly_off', 'leave', 'on_duty', 'work_from_home', 'late', 'missed_punch'],
                'default'    => 'present',
            ],
            'source'              => ['type' => 'ENUM', 'constraint' => ['gps', 'manual', 'biometric', 'regularized'], 'default' => 'manual'],
            'created_by'          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'updated_by'          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'created_at'          => ['type' => 'DATETIME', 'null' => true],
            'updated_at'          => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'          => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['employee_id', 'attendance_date']);
        $this->forge->addKey('attendance_date');
        $this->forge->addKey('status');
        $this->forge->addForeignKey('employee_id', 'employees', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('shift_id', 'attendance_shifts', 'id', '', 'SET NULL');
        $this->forge->createTable('attendance');
    }

    public function down()
    {
        $this->forge->dropTable('attendance');
    }
}
