<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Adds the two attendance statuses Phase 6 needs that Phase 5's enum didn't
 * anticipate: 'half_day_leave' (a leave application's half-day dates) and
 * 'lop' (Loss of Pay leave types). Also adds 'leave' to the `source` enum —
 * essential, not decorative: it's how LeaveAttendanceIntegrationService's
 * cancellation rollback tells "this row was written by leave approval"
 * apart from "HR manually corrected it afterward" (source would be
 * 'manual'/'regularized' in that case), so a cancellation never clobbers a
 * manual fix — see LeaveAttendanceIntegrationService::rollbackFromAttendance().
 */
class AlterAttendanceAddLeaveStatuses extends Migration
{
    public function up()
    {
        $this->forge->modifyColumn('attendance', [
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['present', 'absent', 'half_day', 'holiday', 'weekly_off', 'leave', 'on_duty', 'work_from_home', 'late', 'missed_punch', 'half_day_leave', 'lop'],
                'default'    => 'present',
            ],
            'source' => [
                'type'       => 'ENUM',
                'constraint' => ['gps', 'manual', 'biometric', 'regularized', 'leave'],
                'default'    => 'manual',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->modifyColumn('attendance', [
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['present', 'absent', 'half_day', 'holiday', 'weekly_off', 'leave', 'on_duty', 'work_from_home', 'late', 'missed_punch'],
                'default'    => 'present',
            ],
            'source' => [
                'type'       => 'ENUM',
                'constraint' => ['gps', 'manual', 'biometric', 'regularized'],
                'default'    => 'manual',
            ],
        ]);
    }
}
