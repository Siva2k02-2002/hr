<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/** Single-row table, same pattern as company_settings — see AttendanceSettingsService::current(). */
class CreateAttendanceSettings extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'default_shift_id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'grace_minutes'            => ['type' => 'INT', 'constraint' => 5, 'unsigned' => true, 'default' => 10],
            'late_mark_minutes'        => ['type' => 'INT', 'constraint' => 5, 'unsigned' => true, 'default' => 15],
            'half_day_minutes'         => ['type' => 'INT', 'constraint' => 6, 'unsigned' => true, 'default' => 240],
            'full_day_minutes'         => ['type' => 'INT', 'constraint' => 6, 'unsigned' => true, 'default' => 480],
            'gps_required'             => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'device_approval_required' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'self_attendance_enabled'  => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'overtime_enabled'         => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'weekend_policy'           => ['type' => 'VARCHAR', 'constraint' => 100, 'default' => 'Sunday Off'],
            'holiday_policy'           => ['type' => 'VARCHAR', 'constraint' => 100, 'default' => 'Paid'],
            'timezone'                 => ['type' => 'VARCHAR', 'constraint' => 64, 'default' => 'Asia/Kolkata'],
            'created_at'               => ['type' => 'DATETIME', 'null' => true],
            'updated_at'               => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('default_shift_id', 'attendance_shifts', 'id', '', 'SET NULL');
        $this->forge->createTable('attendance_settings');
    }

    public function down()
    {
        $this->forge->dropTable('attendance_settings');
    }
}
