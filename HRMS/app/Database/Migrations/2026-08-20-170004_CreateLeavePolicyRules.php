<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Per-policy per-leave-type rule set. Nullable numeric fields (carry_forward_limit,
 * max_consecutive_days, notice_period_days) mean "fall back to the leave_settings
 * global" — see LeaveCalculationService/LeaveBalanceService for exactly where each
 * fallback is applied.
 */
class CreateLeavePolicyRules extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'leave_policy_id'           => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'leave_type_id'             => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'annual_allocation'         => ['type' => 'DECIMAL', 'constraint' => '5,1', 'default' => 0],
            'accrual_method'            => ['type' => 'ENUM', 'constraint' => ['annual', 'monthly'], 'default' => 'annual'],
            'monthly_accrual_days'      => ['type' => 'DECIMAL', 'constraint' => '4,2', 'default' => 0],
            'carry_forward_allowed'     => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'carry_forward_limit'       => ['type' => 'DECIMAL', 'constraint' => '5,1', 'null' => true],
            'carry_forward_unlimited'   => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'encashment_allowed'        => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'max_consecutive_days'      => ['type' => 'INT', 'constraint' => 4, 'unsigned' => true, 'null' => true],
            'min_days_per_application'  => ['type' => 'DECIMAL', 'constraint' => '3,1', 'default' => 0.5],
            'max_applications_per_year' => ['type' => 'INT', 'constraint' => 4, 'unsigned' => true, 'null' => true],
            'sandwich_rule_applicable'  => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'notice_period_days'        => ['type' => 'INT', 'constraint' => 3, 'unsigned' => true, 'null' => true],
            'status'                    => ['type' => 'ENUM', 'constraint' => ['active', 'inactive'], 'default' => 'active'],
            'created_at'                => ['type' => 'DATETIME', 'null' => true],
            'updated_at'                => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['leave_policy_id', 'leave_type_id']);
        $this->forge->addForeignKey('leave_policy_id', 'leave_policies', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('leave_type_id', 'leave_types', 'id', '', 'CASCADE');
        $this->forge->createTable('leave_policy_rules');
    }

    public function down()
    {
        $this->forge->dropTable('leave_policy_rules');
    }
}
