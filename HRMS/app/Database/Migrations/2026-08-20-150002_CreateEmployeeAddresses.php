<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateEmployeeAddresses extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'           => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'employee_id'  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'address_type' => ['type' => 'ENUM', 'constraint' => ['permanent', 'current']],
            'country'      => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'state'        => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'city'         => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'district'     => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'pincode'      => ['type' => 'VARCHAR', 'constraint' => 12, 'null' => true],
            'address_line1'=> ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'address_line2'=> ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
            'updated_at'   => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['employee_id', 'address_type']);
        $this->forge->addForeignKey('employee_id', 'employees', 'id', '', 'CASCADE');
        $this->forge->createTable('employee_addresses');
    }

    public function down()
    {
        $this->forge->dropTable('employee_addresses');
    }
}
