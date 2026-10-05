<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/** The person provisioning creates as Company Admin inside the tenant DB. */
class AddPrimaryAdminToCompanies extends Migration
{
    public function up()
    {
        $this->forge->addColumn('companies', [
            'primary_admin_name' => [
                'type' => 'VARCHAR', 'constraint' => 150, 'null' => true, 'after' => 'country',
            ],
            'primary_admin_email' => [
                'type' => 'VARCHAR', 'constraint' => 150, 'null' => true, 'after' => 'primary_admin_name',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('companies', ['primary_admin_name', 'primary_admin_email']);
    }
}
