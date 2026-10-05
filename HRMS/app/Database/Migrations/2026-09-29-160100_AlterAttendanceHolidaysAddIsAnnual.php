<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Backs "Generate Next Year" (AttendanceHolidayService::proposeNextYear()):
 * is_annual=1 (default) means this holiday recurs on the same month/day every
 * year and can be safely auto-proposed for the next year; is_annual=0 means
 * it's a movable/year-specific holiday whose date must be picked by hand each
 * year (e.g. a lunar-calendar festival). Existing rows default to 1 — most
 * holidays already on file are the fixed public ones (New Year's Day,
 * Independence Day, etc.) — but since nothing this flag feeds into ever
 * writes a new holiday without the admin reviewing/confirming the proposed
 * date first, a wrongly-defaulted movable holiday only pre-fills a date that
 * still has to be confirmed, never silently creates one. Any existing
 * holiday that's actually movable can be flipped to "Review each year" from
 * its edit form before the next "Generate Next Year" run.
 */
class AlterAttendanceHolidaysAddIsAnnual extends Migration
{
    public function up()
    {
        $this->forge->addColumn('attendance_holidays', [
            'is_annual' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1, 'after' => 'is_optional'],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('attendance_holidays', 'is_annual');
    }
}
