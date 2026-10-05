<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateEmployeeEmergencyContacts extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'               => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'employee_id'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'name'             => ['type' => 'VARCHAR', 'constraint' => 150],
            'relationship'     => ['type' => 'VARCHAR', 'constraint' => 60],
            'phone'            => ['type' => 'VARCHAR', 'constraint' => 20],
            'alternate_phone'  => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'address'          => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'priority'         => ['type' => 'INT', 'constraint' => 3, 'unsigned' => true, 'default' => 1],
            'created_at'       => ['type' => 'DATETIME', 'null' => true],
            'updated_at'       => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'       => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('employee_id');
        $this->forge->addForeignKey('employee_id', 'employees', 'id', '', 'CASCADE');
        $this->forge->createTable('employee_emergency_contacts');
    }

    public function down()
    {
        $this->forge->dropTable('employee_emergency_contacts');
    }
}
