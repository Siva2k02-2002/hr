<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/** Plain lat/lng + radius, no MySQL spatial types — distance is computed in PHP via GeofenceService's Haversine. */
class CreateAttendanceLocations extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'            => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'name'          => ['type' => 'VARCHAR', 'constraint' => 150],
            'branch_id'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'latitude'      => ['type' => 'DECIMAL', 'constraint' => '10,7'],
            'longitude'     => ['type' => 'DECIMAL', 'constraint' => '10,7'],
            'radius_meters' => ['type' => 'INT', 'constraint' => 6, 'unsigned' => true, 'default' => 200],
            'address'       => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'status'        => ['type' => 'ENUM', 'constraint' => ['active', 'inactive'], 'default' => 'active'],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('branch_id');
        $this->forge->addForeignKey('branch_id', 'branches', 'id', '', 'RESTRICT');
        $this->forge->createTable('attendance_locations');
    }

    public function down()
    {
        $this->forge->dropTable('attendance_locations');
    }
}
