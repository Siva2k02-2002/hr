<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/** Backs EmployeeCodeGenerator's atomic `SELECT ... FOR UPDATE` reservation — see app/Services/EmployeeCodeGenerator.php. */
class AlterCompanySettingsAddEmployeeSequence extends Migration
{
    public function up()
    {
        $this->forge->addColumn('company_settings', [
            'employee_code_prefix'   => ['type' => 'VARCHAR', 'constraint' => 10, 'default' => 'EMP', 'null' => false],
            'employee_code_next_seq' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 1, 'null' => false],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('company_settings', ['employee_code_prefix', 'employee_code_next_seq']);
    }
}
