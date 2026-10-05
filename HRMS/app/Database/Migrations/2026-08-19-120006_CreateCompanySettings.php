<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Single-row table: one tenant database always holds exactly one company,
 * so there is exactly one settings row (id 1). Never store database
 * credentials or platform-level data here — only tenant-facing preferences.
 */
class CreateCompanySettings extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'           => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'company_name' => ['type' => 'VARCHAR', 'constraint' => 150],
            'timezone'     => ['type' => 'VARCHAR', 'constraint' => 64, 'default' => 'Asia/Kolkata'],
            'currency'     => ['type' => 'VARCHAR', 'constraint' => 10, 'default' => 'INR'],
            'date_format'  => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'd-m-Y'],
            'time_format'  => ['type' => 'VARCHAR', 'constraint' => 10, 'default' => '24h'],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
            'updated_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('company_settings');
    }

    public function down()
    {
        $this->forge->dropTable('company_settings');
    }
}
