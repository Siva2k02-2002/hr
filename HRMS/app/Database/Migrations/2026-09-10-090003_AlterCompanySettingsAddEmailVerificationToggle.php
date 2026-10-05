<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterCompanySettingsAddEmailVerificationToggle extends Migration
{
    public function up()
    {
        $this->forge->addColumn('company_settings', [
            // Off by default so existing tenants/users aren't locked out the moment
            // this migration runs — a Company Admin opts in from Settings once every
            // active user's address is known-good.
            'require_email_verification' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0, 'after' => 'time_format'],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('company_settings', ['require_email_verification']);
    }
}
