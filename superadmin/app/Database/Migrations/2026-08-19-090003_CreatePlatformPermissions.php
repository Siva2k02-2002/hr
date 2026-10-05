<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePlatformPermissions extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'slug'        => ['type' => 'VARCHAR', 'constraint' => 150],
            'module'      => ['type' => 'VARCHAR', 'constraint' => 100],
            'description' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('slug');
        $this->forge->addKey('module');
        $this->forge->createTable('platform_permissions');
    }

    public function down()
    {
        $this->forge->dropTable('platform_permissions');
    }
}
