<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/** Super-Admin-configurable grace period (Phase 11 requirement 4) — how many days a tenant keeps access after its license expires before login is blocked. */
class AddGraceDaysToPlans extends Migration
{
    public function up()
    {
        $this->forge->addColumn('plans', [
            'grace_days' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 7, 'after' => 'duration_days'],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('plans', ['grace_days']);
    }
}
