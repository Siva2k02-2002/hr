<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/** branch_id null = applies to every branch. */
class CreateAttendanceHolidays extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'           => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'name'         => ['type' => 'VARCHAR', 'constraint' => 150],
            'date'         => ['type' => 'DATE'],
            'holiday_type' => ['type' => 'ENUM', 'constraint' => ['public', 'restricted', 'company'], 'default' => 'public'],
            'branch_id'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'description'  => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'is_optional'  => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'status'       => ['type' => 'ENUM', 'constraint' => ['active', 'inactive'], 'default' => 'active'],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
            'updated_at'   => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('date');
        $this->forge->addKey('branch_id');
        $this->forge->addForeignKey('branch_id', 'branches', 'id', '', 'SET NULL');
        $this->forge->createTable('attendance_holidays');
    }

    public function down()
    {
        $this->forge->dropTable('attendance_holidays');
    }
}
