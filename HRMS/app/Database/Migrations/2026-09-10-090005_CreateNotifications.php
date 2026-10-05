<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/** In-app notification inbox. Email/SMS/WhatsApp/push are separate delivery channels NotificationService fires alongside this — this table is only the in-app record. */
class CreateNotifications extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'user_id'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'type'       => ['type' => 'VARCHAR', 'constraint' => 60],
            'title'      => ['type' => 'VARCHAR', 'constraint' => 150],
            'body'       => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'url'        => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'data'       => ['type' => 'TEXT', 'null' => true],
            'read_at'    => ['type' => 'DATETIME', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['user_id', 'read_at']);
        $this->forge->addForeignKey('user_id', 'users', 'id', '', 'CASCADE');
        $this->forge->createTable('notifications');
    }

    public function down()
    {
        $this->forge->dropTable('notifications');
    }
}
