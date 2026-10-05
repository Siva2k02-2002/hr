<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Phase 17: lets an audit event be tagged with the employee it's *about*,
 * independent of which table record_id points to — a leave-approval event's
 * record_id is the leave_application's id, not the employee's, so there was
 * no way to query "every audit event touching employee X" across modules.
 * Nullable and populated only where the acting service now passes it
 * (EmployeeService, LeaveApplicationService, LeaveApprovalService,
 * AttendanceRegularizationService) — existing rows stay NULL, not backfilled.
 */
class AddEmployeeIdToAuditLogs extends Migration
{
    public function up()
    {
        $this->forge->addColumn('audit_logs', [
            'employee_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true, 'after' => 'user_id'],
        ]);
        $this->forge->addKey('employee_id', false, false, 'idx_audit_logs_employee_id');
        $this->forge->processIndexes('audit_logs');
    }

    public function down()
    {
        $this->db->query('ALTER TABLE audit_logs DROP INDEX idx_audit_logs_employee_id');
        $this->forge->dropColumn('audit_logs', ['employee_id']);
    }
}
