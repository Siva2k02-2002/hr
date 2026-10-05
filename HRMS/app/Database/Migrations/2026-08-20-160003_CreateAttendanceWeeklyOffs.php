<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/** week_pattern='alternate' means 2nd & 4th (the standard convention) — see WeeklyOffService. */
class CreateAttendanceWeeklyOffs extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'           => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'name'         => ['type' => 'VARCHAR', 'constraint' => 100],
            'day_of_week'  => ['type' => 'ENUM', 'constraint' => ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday']],
            'week_pattern' => ['type' => 'ENUM', 'constraint' => ['every', 'first', 'second', 'third', 'fourth', 'fifth', 'alternate'], 'default' => 'every'],
            'branch_id'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'shift_id'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'status'       => ['type' => 'ENUM', 'constraint' => ['active', 'inactive'], 'default' => 'active'],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
            'updated_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('branch_id');
        $this->forge->addForeignKey('branch_id', 'branches', 'id', '', 'SET NULL');
        $this->forge->addForeignKey('shift_id', 'attendance_shifts', 'id', '', 'SET NULL');
        $this->forge->createTable('attendance_weekly_offs');
    }

    public function down()
    {
        $this->forge->dropTable('attendance_weekly_offs');
    }
}
