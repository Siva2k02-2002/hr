<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateEmployeeFamilyMembers extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'           => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'employee_id'  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'relationship' => ['type' => 'VARCHAR', 'constraint' => 60],
            'name'         => ['type' => 'VARCHAR', 'constraint' => 150],
            'dob'          => ['type' => 'DATE', 'null' => true],
            'occupation'   => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'is_dependent' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'is_nominee'   => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
            'updated_at'   => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('employee_id');
        $this->forge->addForeignKey('employee_id', 'employees', 'id', '', 'CASCADE');
        $this->forge->createTable('employee_family_members');
    }

    public function down()
    {
        $this->forge->dropTable('employee_family_members');
    }
}
