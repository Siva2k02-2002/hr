<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * One row per issued license. A company can accumulate several over time
 * (renewals insert a new row rather than mutating expires_at in place, so
 * the full history — and every signature ever issued — stays intact for
 * audit). LicenseModel::currentFor() always resolves the most recent row.
 */
class CreateLicenses extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'             => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'license_uuid'   => ['type' => 'CHAR', 'constraint' => 36],
            'license_key'    => ['type' => 'VARCHAR', 'constraint' => 40],
            'company_id'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'plan_id'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'domain'         => ['type' => 'VARCHAR', 'constraint' => 191],
            'issued_at'      => ['type' => 'DATETIME'],
            'expires_at'     => ['type' => 'DATETIME'],
            'grace_days'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 7],
            'status'         => ['type' => 'ENUM', 'constraint' => ['active', 'revoked', 'expired'], 'default' => 'active'],
            'signature'      => ['type' => 'CHAR', 'constraint' => 64],
            'issued_by'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'revoked_at'     => ['type' => 'DATETIME', 'null' => true],
            'revoked_by'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'revoked_reason' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
            'updated_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('license_uuid');
        $this->forge->addUniqueKey('license_key');
        $this->forge->addKey(['company_id', 'status']);
        $this->forge->addForeignKey('company_id', 'companies', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('plan_id', 'plans', 'id', '', 'RESTRICT');
        $this->forge->createTable('licenses');
    }

    public function down()
    {
        $this->forge->dropTable('licenses');
    }
}
