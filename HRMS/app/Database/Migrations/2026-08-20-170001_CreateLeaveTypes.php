<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Master list of leave types (CL/SL/EL/LOP/Comp Off/WFH/On Duty/...). Type-level
 * flags (carry_forward_allowed, encashment_allowed, annual_allocation) are master
 * gates/defaults — the operative per-assignment numbers live on leave_policy_rules
 * and can only be more restrictive, never less, than what's allowed here.
 */
class CreateLeaveTypes extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                            => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'name'                          => ['type' => 'VARCHAR', 'constraint' => 100],
            'code'                          => ['type' => 'VARCHAR', 'constraint' => 20],
            'description'                   => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'color'                         => ['type' => 'VARCHAR', 'constraint' => 7, 'default' => '#4B3FD1'],
            'is_paid'                       => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'annual_allocation'             => ['type' => 'DECIMAL', 'constraint' => '5,1', 'default' => 0],
            'half_day_allowed'              => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'attachment_required'           => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'medical_certificate_required'  => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'carry_forward_allowed'         => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'encashment_allowed'            => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'attendance_status_map'         => ['type' => 'ENUM', 'constraint' => ['leave', 'lop', 'work_from_home', 'on_duty'], 'default' => 'leave'],
            'sort_order'                    => ['type' => 'INT', 'constraint' => 5, 'default' => 0],
            'status'                        => ['type' => 'ENUM', 'constraint' => ['active', 'inactive'], 'default' => 'active'],
            'created_by'                    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'updated_by'                    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'created_at'                    => ['type' => 'DATETIME', 'null' => true],
            'updated_at'                    => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'                    => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('code');
        $this->forge->addKey('status');
        $this->forge->createTable('leave_types');
    }

    public function down()
    {
        $this->forge->dropTable('leave_types');
    }
}
