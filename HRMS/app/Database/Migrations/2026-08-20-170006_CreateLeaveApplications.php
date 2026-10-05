<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * balance_deducted / attendance_applied are idempotency guards — see
 * LeaveBalanceService::deductOnApproval()/restoreOnCancellation() and
 * LeaveAttendanceIntegrationService — that prevent a double-deduct or a
 * repeat attendance write across retries/re-approval races. level1_* is
 * snapshotted at submit (the reporting manager's user at that moment);
 * level2 is resolved live at act-time (any hr-manager), so it has no
 * *_approver_id column — see LeaveApprovalService.
 */
class CreateLeaveApplications extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'employee_id'            => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'leave_type_id'          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'leave_policy_id'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'from_date'              => ['type' => 'DATE'],
            'to_date'                => ['type' => 'DATE'],
            'is_half_day'            => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'half_day_session'       => ['type' => 'ENUM', 'constraint' => ['first_half', 'second_half'], 'null' => true],
            'total_days'             => ['type' => 'DECIMAL', 'constraint' => '5,1', 'default' => 0],
            'reason'                 => ['type' => 'VARCHAR', 'constraint' => 500],
            'emergency_contact_name' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'emergency_contact_phone'=> ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'attachment_path'        => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'is_emergency'           => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'status'                 => ['type' => 'ENUM', 'constraint' => ['draft', 'pending', 'approved', 'rejected', 'cancelled'], 'default' => 'draft'],
            'current_level'          => ['type' => 'ENUM', 'constraint' => ['level1', 'level2', 'completed'], 'default' => 'level1'],
            'level1_approver_id'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'level1_status'          => ['type' => 'ENUM', 'constraint' => ['pending', 'approved', 'rejected', 'skipped'], 'default' => 'pending'],
            'level1_acted_by'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'level1_acted_at'        => ['type' => 'DATETIME', 'null' => true],
            'level1_remarks'         => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'level2_status'          => ['type' => 'ENUM', 'constraint' => ['pending', 'approved', 'rejected', 'skipped'], 'default' => 'pending'],
            'level2_acted_by'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'level2_acted_at'        => ['type' => 'DATETIME', 'null' => true],
            'level2_remarks'         => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'balance_deducted'       => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'attendance_applied'     => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'cancelled_by'           => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'cancelled_at'           => ['type' => 'DATETIME', 'null' => true],
            'cancellation_reason'    => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'submitted_at'           => ['type' => 'DATETIME', 'null' => true],
            'created_by'             => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'updated_by'             => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'created_at'             => ['type' => 'DATETIME', 'null' => true],
            'updated_at'             => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'             => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('employee_id');
        $this->forge->addKey('leave_type_id');
        $this->forge->addKey('status');
        $this->forge->addKey('from_date');
        $this->forge->addKey('to_date');
        $this->forge->addKey(['employee_id', 'status']);

        $this->forge->addForeignKey('employee_id', 'employees', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('leave_type_id', 'leave_types', 'id', '', 'RESTRICT');
        $this->forge->addForeignKey('leave_policy_id', 'leave_policies', 'id', '', 'SET NULL');
        $this->forge->addForeignKey('level1_approver_id', 'users', 'id', '', 'SET NULL');
        $this->forge->addForeignKey('level1_acted_by', 'users', 'id', '', 'SET NULL');
        $this->forge->addForeignKey('level2_acted_by', 'users', 'id', '', 'SET NULL');
        $this->forge->addForeignKey('cancelled_by', 'users', 'id', '', 'SET NULL');
        $this->forge->addForeignKey('created_by', 'users', 'id', '', 'SET NULL');
        $this->forge->addForeignKey('updated_by', 'users', 'id', '', 'SET NULL');

        $this->forge->createTable('leave_applications');
    }

    public function down()
    {
        $this->forge->dropTable('leave_applications');
    }
}
