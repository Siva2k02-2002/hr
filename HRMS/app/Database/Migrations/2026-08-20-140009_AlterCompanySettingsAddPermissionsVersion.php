<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Bumped whenever any role's permissions or a user's role assignment
 * changes. TenantAuthFilter compares this (one indexed single-row lookup)
 * against the version the session's cached permission list was built
 * with, and only recomputes when it's stale — avoids re-querying
 * role/permission joins on every request while still invalidating
 * correctly the moment access actually changes.
 */
class AlterCompanySettingsAddPermissionsVersion extends Migration
{
    public function up()
    {
        $this->forge->addColumn('company_settings', [
            'permissions_version' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 1],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('company_settings', ['permissions_version']);
    }
}
