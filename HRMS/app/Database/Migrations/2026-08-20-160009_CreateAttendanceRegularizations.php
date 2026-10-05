<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAttendanceRegularizations extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'employee_id'           => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'attendance_date'       => ['type' => 'DATE'],
            'reason'                => ['type' => 'VARCHAR', 'constraint' => 255],
            'requested_punch_in'    => ['type' => 'TIME', 'null' => true],
            'requested_punch_out'   => ['type' => 'TIME', 'null' => true],
            'attachment_path'       => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'status'                => ['type' => 'ENUM', 'constraint' => ['pending', 'approved', 'rejected'], 'default' => 'pending'],
            'reviewed_by'           => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'reviewed_at'           => ['type' => 'DATETIME', 'null' => true],
            'review_remarks'        => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_by'            => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'created_at'            => ['type' => 'DATETIME', 'null' => true],
            'updated_at'            => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['employee_id', 'attendance_date']);
        $this->forge->addForeignKey('employee_id', 'employees', 'id', '', 'CASCADE');
        $this->forge->createTable('attendance_regularizations');
    }

    public function down()
    {
        $this->forge->dropTable('attendance_regularizations');
    }
}
