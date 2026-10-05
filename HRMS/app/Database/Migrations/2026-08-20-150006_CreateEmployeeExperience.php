<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateEmployeeExperience extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'employee_id'            => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'company_name'           => ['type' => 'VARCHAR', 'constraint' => 200],
            'designation'            => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'from_date'              => ['type' => 'DATE'],
            'to_date'                => ['type' => 'DATE', 'null' => true],
            'years_experience'       => ['type' => 'DECIMAL', 'constraint' => '4,1', 'null' => true],
            'reason_for_leaving'     => ['type' => 'TEXT', 'null' => true],
            'experience_letter_path' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at'             => ['type' => 'DATETIME', 'null' => true],
            'updated_at'             => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'             => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('employee_id');
        $this->forge->addForeignKey('employee_id', 'employees', 'id', '', 'CASCADE');
        $this->forge->createTable('employee_experience');
    }

    public function down()
    {
        $this->forge->dropTable('employee_experience');
    }
}
