<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/** Immutable, append-only ledger — no updated_at/deleted_at, same reasoning as audit_logs. */
class CreateLeaveApprovalHistory extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'leave_application_id'  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'action'                => [
                'type'       => 'ENUM',
                'constraint' => ['submitted', 'level1_approved', 'level1_rejected', 'level2_approved', 'level2_rejected', 'admin_override_approved', 'admin_override_rejected', 'cancelled', 'delegation_assigned'],
            ],
            'level'                 => ['type' => 'ENUM', 'constraint' => ['level1', 'level2', 'admin', 'system'], 'null' => true],
            'actor_id'              => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'actor_role'            => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'remarks'               => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'override_reason'       => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'snapshot_status'       => ['type' => 'VARCHAR', 'constraint' => 20],
            'created_at'            => ['type' => 'DATETIME'],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('leave_application_id');
        $this->forge->addKey('created_at');
        $this->forge->addForeignKey('leave_application_id', 'leave_applications', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('actor_id', 'users', 'id', '', 'SET NULL');
        $this->forge->createTable('leave_approval_history');
    }

    public function down()
    {
        $this->forge->dropTable('leave_approval_history');
    }
}
