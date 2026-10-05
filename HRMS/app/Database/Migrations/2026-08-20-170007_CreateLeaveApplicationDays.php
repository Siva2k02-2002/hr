<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Day-level breakdown of a leave_application — one row per calendar date in
 * its range. Fully recomputed/reinserted by LeaveCalculationService whenever
 * an application is (re)calculated — never hand-edited, so no soft delete.
 */
class CreateLeaveApplicationDays extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'leave_application_id'  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'leave_date'            => ['type' => 'DATE'],
            'day_type'              => ['type' => 'ENUM', 'constraint' => ['full', 'half_first', 'half_second'], 'default' => 'full'],
            'day_category'          => ['type' => 'ENUM', 'constraint' => ['working', 'weekend', 'holiday'], 'default' => 'working'],
            'is_sandwiched'         => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'counts_as_leave'       => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'day_value'             => ['type' => 'DECIMAL', 'constraint' => '3,1', 'default' => 1.0],
            'holiday_id'            => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'created_at'            => ['type' => 'DATETIME', 'null' => true],
            'updated_at'            => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['leave_application_id', 'leave_date']);
        $this->forge->addKey('leave_date');
        $this->forge->addForeignKey('leave_application_id', 'leave_applications', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('holiday_id', 'attendance_holidays', 'id', '', 'SET NULL');
        $this->forge->createTable('leave_application_days');
    }

    public function down()
    {
        $this->forge->dropTable('leave_application_days');
    }
}
