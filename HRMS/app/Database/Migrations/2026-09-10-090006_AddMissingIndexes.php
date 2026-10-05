<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Phase 14 database review findings:
 *  - login_logs.user_id: UserService/AuthController's "this user's login history"
 *    queries filter by it, but only `email` was indexed — full scan on a table
 *    that grows unbounded.
 *  - audit_logs (module, record_id): the "history for this record" admin view
 *    filters both together; only `module` alone was indexed.
 */
class AddMissingIndexes extends Migration
{
    public function up()
    {
        $this->forge->addKey('user_id', false, false, 'idx_login_logs_user_id');
        $this->forge->processIndexes('login_logs');

        $this->forge->addKey(['module', 'record_id'], false, false, 'idx_audit_logs_module_record_id');
        $this->forge->processIndexes('audit_logs');
    }

    public function down()
    {
        $this->db->query('ALTER TABLE login_logs DROP INDEX idx_login_logs_user_id');
        $this->db->query('ALTER TABLE audit_logs DROP INDEX idx_audit_logs_module_record_id');
    }
}
