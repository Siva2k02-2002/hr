<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePlatformSettings extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'setting_key'   => ['type' => 'VARCHAR', 'constraint' => 100],
            'setting_value' => ['type' => 'TEXT', 'null' => true],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('setting_key');
        $this->forge->createTable('platform_settings');
    }

    public function down()
    {
        $this->forge->dropTable('platform_settings');
    }
}
