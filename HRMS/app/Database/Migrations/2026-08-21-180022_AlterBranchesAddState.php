<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/** Nullable — needed so PayrollProfessionalTaxService can resolve the state-wise PT slab for an employee via their branch. Existing branches are unaffected until someone sets it. */
class AlterBranchesAddState extends Migration
{
    public function up()
    {
        $this->forge->addColumn('branches', [
            'state' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true, 'after' => 'address'],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('branches', 'state');
    }
}
