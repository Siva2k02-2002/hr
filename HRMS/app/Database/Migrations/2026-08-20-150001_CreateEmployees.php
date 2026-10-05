<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * The Employee Master. Deliberately separate from `users` (the Phase 3
 * login credential table) — not every employee has system access yet, and
 * not every login belongs to an employee (e.g. an integration account).
 * `user_id` is the optional link between the two. `reporting_manager_id`
 * is self-referential so org-hierarchy logic never has to care whether the
 * manager also has a login.
 */
class CreateEmployees extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],

            'employee_code'          => ['type' => 'VARCHAR', 'constraint' => 20],

            'first_name'             => ['type' => 'VARCHAR', 'constraint' => 100],
            'middle_name'            => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'last_name'              => ['type' => 'VARCHAR', 'constraint' => 100],
            'gender'                 => ['type' => 'ENUM', 'constraint' => ['male', 'female', 'other'], 'null' => true],
            'date_of_birth'          => ['type' => 'DATE', 'null' => true],
            'blood_group'            => ['type' => 'VARCHAR', 'constraint' => 5, 'null' => true],
            'marital_status'         => ['type' => 'ENUM', 'constraint' => ['single', 'married', 'divorced', 'widowed'], 'null' => true],
            'nationality'            => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'aadhaar_number'         => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'pan_number'             => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'passport_number'        => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
            'driving_license_number' => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],

            'mobile'                 => ['type' => 'VARCHAR', 'constraint' => 20],
            'alternate_mobile'       => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'personal_email'         => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'company_email'          => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],

            'branch_id'              => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'department_id'          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'designation_id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'reporting_manager_id'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'employment_type'        => ['type' => 'ENUM', 'constraint' => ['full_time', 'part_time', 'contract', 'intern', 'consultant'], 'default' => 'full_time'],
            'employment_category'    => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'shift'                  => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'work_location'          => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'date_of_joining'        => ['type' => 'DATE'],
            'date_of_confirmation'   => ['type' => 'DATE', 'null' => true],
            'probation_period_months'=> ['type' => 'INT', 'constraint' => 3, 'unsigned' => true, 'null' => true],

            /**
             * The spec's "Status Values" list and its "Status Badges" list disagree
             * (the former has Absconded but no Suspended, the latter the reverse) —
             * both are kept since the CRUD section explicitly requires a Suspend action.
             */
            'status'                 => [
                'type'       => 'ENUM',
                'constraint' => ['active', 'probation', 'notice_period', 'suspended', 'resigned', 'terminated', 'retired', 'absconded', 'relieved'],
                'default'    => 'probation',
            ],

            'photo_path'             => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'user_id'                => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],

            'created_by'             => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'updated_by'             => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'created_at'             => ['type' => 'DATETIME', 'null' => true],
            'updated_at'             => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'             => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('employee_code');
        $this->forge->addUniqueKey('company_email');
        $this->forge->addUniqueKey('personal_email');

        $this->forge->addKey('branch_id');
        $this->forge->addKey('department_id');
        $this->forge->addKey('designation_id');
        $this->forge->addKey('reporting_manager_id');
        $this->forge->addKey('status');
        $this->forge->addKey('user_id');

        $this->forge->addForeignKey('branch_id', 'branches', 'id', '', 'RESTRICT');
        $this->forge->addForeignKey('department_id', 'departments', 'id', '', 'RESTRICT');
        $this->forge->addForeignKey('designation_id', 'designations', 'id', '', 'RESTRICT');
        $this->forge->addForeignKey('reporting_manager_id', 'employees', 'id', '', 'SET NULL');
        $this->forge->addForeignKey('user_id', 'users', 'id', '', 'SET NULL');

        $this->forge->createTable('employees');
    }

    public function down()
    {
        $this->forge->dropTable('employees');
    }
}
