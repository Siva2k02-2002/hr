<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Auto-registered as 'pending' on an employee's first punch from a new
 * device (see AttendanceDeviceService) — approval is a retroactive HR
 * review flag, it never blocks the punch itself.
 */
class CreateAttendanceDevices extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'             => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'employee_id'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'device_uid'     => ['type' => 'VARCHAR', 'constraint' => 100],
            'device_name'    => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'browser'        => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'os'             => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'user_agent'     => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'ip_address'     => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true],
            'first_login_at' => ['type' => 'DATETIME', 'null' => true],
            'last_login_at'  => ['type' => 'DATETIME', 'null' => true],
            'status'         => ['type' => 'ENUM', 'constraint' => ['pending', 'approved', 'rejected', 'blocked'], 'default' => 'pending'],
            'approved_by'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'approved_at'    => ['type' => 'DATETIME', 'null' => true],
            'remarks'        => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
            'updated_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['employee_id', 'device_uid']);
        $this->forge->addForeignKey('employee_id', 'employees', 'id', '', 'CASCADE');
        $this->forge->createTable('attendance_devices');
    }

    public function down()
    {
        $this->forge->dropTable('attendance_devices');
    }
}
