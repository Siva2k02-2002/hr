<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Scope is nullable columns directly on the table — same convention as
 * attendance_holidays.branch_id (null = applies to all). Exactly one row
 * with every scope column null should have is_default=1 (enforced in
 * LeavePolicyService, not the DB). Resolution order (most specific wins):
 * employee_id > designation_id > department_id > branch_id > employment_type
 * > company-wide default.
 */
class CreateLeavePolicies extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'              => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'name'            => ['type' => 'VARCHAR', 'constraint' => 150],
            'description'     => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'branch_id'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'department_id'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'designation_id'  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'employment_type' => ['type' => 'ENUM', 'constraint' => ['full_time', 'part_time', 'contract', 'intern', 'consultant'], 'null' => true],
            'employee_id'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'is_default'      => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'effective_from'  => ['type' => 'DATE', 'null' => true],
            'effective_to'    => ['type' => 'DATE', 'null' => true],
            'priority'        => ['type' => 'INT', 'constraint' => 5, 'default' => 0],
            'status'          => ['type' => 'ENUM', 'constraint' => ['active', 'inactive'], 'default' => 'active'],
            'created_by'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'updated_by'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
            'updated_at'      => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('branch_id');
        $this->forge->addKey('department_id');
        $this->forge->addKey('designation_id');
        $this->forge->addKey('employee_id');
        $this->forge->addKey('status');

        $this->forge->addForeignKey('branch_id', 'branches', 'id', '', 'SET NULL');
        $this->forge->addForeignKey('department_id', 'departments', 'id', '', 'SET NULL');
        $this->forge->addForeignKey('designation_id', 'designations', 'id', '', 'SET NULL');
        $this->forge->addForeignKey('employee_id', 'employees', 'id', '', 'CASCADE');

        $this->forge->createTable('leave_policies');
    }

    public function down()
    {
        $this->forge->dropTable('leave_policies');
    }
}
