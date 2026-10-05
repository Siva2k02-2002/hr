<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterUsersAddPhase3Fields extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'username'               => ['type' => 'VARCHAR', 'constraint' => 60, 'null' => true, 'after' => 'name'],
            'mobile'                 => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true, 'after' => 'email'],
            'employee_id'            => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true, 'after' => 'mobile'],
            'branch_id'              => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true, 'after' => 'employee_id'],
            'department_id'          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true, 'after' => 'branch_id'],
            'designation_id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true, 'after' => 'department_id'],
            'failed_login_attempts'  => ['type' => 'TINYINT', 'constraint' => 3, 'unsigned' => true, 'default' => 0, 'after' => 'must_change_password'],
            'locked_until'           => ['type' => 'DATETIME', 'null' => true, 'after' => 'failed_login_attempts'],
            'last_login_ip'          => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true, 'after' => 'last_login_at'],
            'last_active_at'         => ['type' => 'DATETIME', 'null' => true, 'after' => 'last_login_ip'],
            'session_version'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 1, 'after' => 'last_active_at'],
            'password_changed_at'    => ['type' => 'DATETIME', 'null' => true, 'after' => 'session_version'],
            'reset_token_hash'       => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true, 'after' => 'password_changed_at'],
            'reset_token_expires_at' => ['type' => 'DATETIME', 'null' => true, 'after' => 'reset_token_hash'],
            'created_by'             => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true, 'after' => 'reset_token_expires_at'],
            'updated_by'             => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true, 'after' => 'created_by'],
            'deleted_at'             => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addUniqueKey('username', 'users_username_unique');
        $this->forge->addForeignKey('branch_id', 'branches', 'id', '', 'SET NULL');
        $this->forge->addForeignKey('department_id', 'departments', 'id', '', 'SET NULL');
        $this->forge->addForeignKey('designation_id', 'designations', 'id', '', 'SET NULL');
        $this->forge->processIndexes('users');
    }

    public function down()
    {
        $this->forge->dropForeignKey('users', 'users_branch_id_foreign');
        $this->forge->dropForeignKey('users', 'users_department_id_foreign');
        $this->forge->dropForeignKey('users', 'users_designation_id_foreign');
        $this->forge->dropColumn('users', [
            'username', 'mobile', 'employee_id', 'branch_id', 'department_id', 'designation_id',
            'failed_login_attempts', 'locked_until', 'last_login_ip', 'last_active_at', 'session_version',
            'password_changed_at', 'reset_token_hash', 'reset_token_expires_at', 'created_by', 'updated_by', 'deleted_at',
        ]);
    }
}
