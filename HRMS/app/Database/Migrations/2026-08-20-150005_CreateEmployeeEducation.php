<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateEmployeeEducation extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'               => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'employee_id'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'qualification'    => ['type' => 'VARCHAR', 'constraint' => 150],
            'institution'      => ['type' => 'VARCHAR', 'constraint' => 200],
            'board_university' => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true],
            'percentage_cgpa'  => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'year_of_passing'  => ['type' => 'SMALLINT', 'constraint' => 6, 'unsigned' => true, 'null' => true],
            'certificate_path' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at'       => ['type' => 'DATETIME', 'null' => true],
            'updated_at'       => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'       => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('employee_id');
        $this->forge->addForeignKey('employee_id', 'employees', 'id', '', 'CASCADE');
        $this->forge->createTable('employee_education');
    }

    public function down()
    {
        $this->forge->dropTable('employee_education');
    }
}
