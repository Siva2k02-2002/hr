<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Separate from `status` (trial/active/expiring/expired/suspended/cancelled,
 * which is the subscription/business lifecycle) — provisioning_status
 * tracks whether the tenant database actually exists and is usable yet.
 * A company must never be reachable by its tenant users until this is
 * 'ready', regardless of what `status` says.
 */
class AddProvisioningToCompanies extends Migration
{
    public function up()
    {
        $this->forge->addColumn('companies', [
            'provisioning_status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'provisioning', 'ready', 'failed'],
                'default'    => 'pending',
                'after'      => 'status',
            ],
            'provisioning_error' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'provisioning_status',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('companies', ['provisioning_status', 'provisioning_error']);
    }
}
