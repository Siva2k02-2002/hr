<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAttendanceShifts extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'name'              => ['type' => 'VARCHAR', 'constraint' => 100],
            'code'              => ['type' => 'VARCHAR', 'constraint' => 20],
            'start_time'        => ['type' => 'TIME'],
            'end_time'          => ['type' => 'TIME'],
            'break_start'       => ['type' => 'TIME', 'null' => true],
            'break_end'         => ['type' => 'TIME', 'null' => true],
            'grace_minutes'     => ['type' => 'INT', 'constraint' => 5, 'unsigned' => true, 'default' => 0],
            'late_minutes'      => ['type' => 'INT', 'constraint' => 5, 'unsigned' => true, 'default' => 0],
            'half_day_minutes'  => ['type' => 'INT', 'constraint' => 6, 'unsigned' => true, 'default' => 240],
            'full_day_minutes'  => ['type' => 'INT', 'constraint' => 6, 'unsigned' => true, 'default' => 480],
            'is_night_shift'    => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'status'            => ['type' => 'ENUM', 'constraint' => ['active', 'inactive'], 'default' => 'active'],
            'created_at'        => ['type' => 'DATETIME', 'null' => true],
            'updated_at'        => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'        => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('code');
        $this->forge->createTable('attendance_shifts');
    }

    public function down()
    {
        $this->forge->dropTable('attendance_shifts');
    }
}
