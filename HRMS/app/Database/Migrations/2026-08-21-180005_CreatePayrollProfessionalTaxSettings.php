<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/** State-wise slab table — not a singleton. Seeded rows for Tamil Nadu/Karnataka/Telangana/Andhra Pradesh; more states can be added without a schema change. */
class CreatePayrollProfessionalTaxSettings extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'              => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'state'           => ['type' => 'VARCHAR', 'constraint' => 50],
            'min_gross'       => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0],
            'max_gross'       => ['type' => 'DECIMAL', 'constraint' => '10,2', 'null' => true],
            'tax_amount'      => ['type' => 'DECIMAL', 'constraint' => '8,2', 'default' => 0],
            'effective_from'  => ['type' => 'DATE'],
            'status'          => ['type' => 'ENUM', 'constraint' => ['active', 'inactive'], 'default' => 'active'],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
            'updated_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('state');
        $this->forge->addKey('status');
        $this->forge->createTable('payroll_professional_tax_settings');
    }

    public function down()
    {
        $this->forge->dropTable('payroll_professional_tax_settings');
    }
}
