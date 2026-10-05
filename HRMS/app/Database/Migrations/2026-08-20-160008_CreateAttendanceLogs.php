<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/** Every raw punch event — many rows/day possible. attendance_id links back once the day's aggregate exists. */
class CreateAttendanceLogs extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'              => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'employee_id'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'attendance_id'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'punch_type'      => ['type' => 'ENUM', 'constraint' => ['in', 'out']],
            'punch_time'      => ['type' => 'DATETIME'],
            'latitude'        => ['type' => 'DECIMAL', 'constraint' => '10,7', 'null' => true],
            'longitude'       => ['type' => 'DECIMAL', 'constraint' => '10,7', 'null' => true],
            'accuracy_meters' => ['type' => 'DECIMAL', 'constraint' => '8,2', 'null' => true],
            'device_id'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'location_id'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'distance_meters' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'null' => true],
            'geofence_status' => ['type' => 'ENUM', 'constraint' => ['inside', 'outside', 'gps_disabled', 'low_accuracy', 'not_applicable'], 'default' => 'not_applicable'],
            'ip_address'      => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true],
            'source'          => ['type' => 'ENUM', 'constraint' => ['gps', 'manual', 'biometric'], 'default' => 'gps'],
            'remarks'         => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['employee_id', 'punch_time']);
        $this->forge->addForeignKey('employee_id', 'employees', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('attendance_id', 'attendance', 'id', '', 'SET NULL');
        $this->forge->addForeignKey('device_id', 'attendance_devices', 'id', '', 'SET NULL');
        $this->forge->addForeignKey('location_id', 'attendance_locations', 'id', '', 'SET NULL');
        $this->forge->createTable('attendance_logs');
    }

    public function down()
    {
        $this->forge->dropTable('attendance_logs');
    }
}
