<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/** Account number is stored as given and masked only at display time — see mask_account_number() in employee_helper.php. */
class CreateEmployeeBankAccounts extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'employee_id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'account_holder_name' => ['type' => 'VARCHAR', 'constraint' => 150],
            'bank_name'           => ['type' => 'VARCHAR', 'constraint' => 150],
            'branch_name'         => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'account_number'      => ['type' => 'VARCHAR', 'constraint' => 30],
            'ifsc_code'           => ['type' => 'VARCHAR', 'constraint' => 15],
            'upi_id'              => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'is_primary'          => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at'          => ['type' => 'DATETIME', 'null' => true],
            'updated_at'          => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'          => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('employee_id');
        $this->forge->addForeignKey('employee_id', 'employees', 'id', '', 'CASCADE');
        $this->forge->createTable('employee_bank_accounts');
    }

    public function down()
    {
        $this->forge->dropTable('employee_bank_accounts');
    }
}
