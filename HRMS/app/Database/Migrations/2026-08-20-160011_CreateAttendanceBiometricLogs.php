<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Staging table for the "biometric-ready" architecture — no hardware talks
 * to this app yet. AttendanceBiometricImportService parses an uploaded
 * device export into this table, resolves employee_code -> employee_id,
 * then syncs matched rows into attendance_logs the same way a GPS punch
 * would (see AttendancePunchService). Ready for ZKTeco/eSSL/Matrix later.
 */
class CreateAttendanceBiometricLogs extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'device_serial'       => ['type' => 'VARCHAR', 'constraint' => 100],
            'employee_code'       => ['type' => 'VARCHAR', 'constraint' => 30],
            'punch_time'          => ['type' => 'DATETIME'],
            'punch_type'          => ['type' => 'ENUM', 'constraint' => ['in', 'out'], 'null' => true],
            'raw_payload'         => ['type' => 'TEXT', 'null' => true],
            'sync_status'         => ['type' => 'ENUM', 'constraint' => ['pending', 'synced', 'failed', 'ignored'], 'default' => 'pending'],
            'synced_employee_id'  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'created_at'          => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('employee_code');
        $this->forge->addKey('sync_status');
        $this->forge->addForeignKey('synced_employee_id', 'employees', 'id', '', 'SET NULL');
        $this->forge->createTable('attendance_biometric_logs');
    }

    public function down()
    {
        $this->forge->dropTable('attendance_biometric_logs');
    }
}
