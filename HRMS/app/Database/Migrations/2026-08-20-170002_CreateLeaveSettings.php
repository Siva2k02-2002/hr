<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/** Single-row config table, same pattern as attendance_settings/company_settings. */
class CreateLeaveSettings extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                              => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'financial_year_start_month'      => ['type' => 'INT', 'constraint' => 2, 'default' => 1],
            'leave_year_start_month'          => ['type' => 'INT', 'constraint' => 2, 'default' => 1],
            'half_day_enabled'                => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'sandwich_leave_enabled'          => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'holiday_between_leave_policy'    => ['type' => 'ENUM', 'constraint' => ['count', 'not_count'], 'default' => 'not_count'],
            'weekly_off_between_leave_policy' => ['type' => 'ENUM', 'constraint' => ['count', 'not_count'], 'default' => 'not_count'],
            'carry_forward_enabled'           => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'carry_forward_limit'             => ['type' => 'DECIMAL', 'constraint' => '5,1', 'default' => 0],
            'carry_forward_expiry_month'      => ['type' => 'INT', 'constraint' => 2, 'null' => true],
            'leave_encashment_enabled'        => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'max_consecutive_leave'           => ['type' => 'INT', 'constraint' => 4, 'null' => true],
            'min_notice_days'                 => ['type' => 'INT', 'constraint' => 3, 'default' => 0],
            'max_future_apply_days'           => ['type' => 'INT', 'constraint' => 4, 'default' => 90],
            'allow_negative_balance'          => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'self_approval_allowed_for_admin' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'half_day_hours'                  => ['type' => 'DECIMAL', 'constraint' => '3,1', 'default' => 4.0],
            'created_at'                      => ['type' => 'DATETIME', 'null' => true],
            'updated_at'                      => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('leave_settings');
    }

    public function down()
    {
        $this->forge->dropTable('leave_settings');
    }
}
