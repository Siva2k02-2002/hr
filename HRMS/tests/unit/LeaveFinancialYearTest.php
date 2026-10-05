<?php

use CodeIgniter\Test\CIUnitTestCase;

/**
 * Pure-logic coverage for leave_helper.php's financial-year math — the
 * $startMonth override lets every case here run without a tenant DB
 * connection, since it skips the LeaveSettingModel(service('tenantContext')->db()) lookup.
 *
 * @internal
 */
final class LeaveFinancialYearTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        helper('leave');
    }

    public function testAprilStartFinancialYearBeforeApril(): void
    {
        // 2026-03-31 is still FY2025 (2025-04-01 .. 2026-03-31) under an April-start year.
        $this->assertSame(2025, leave_financial_year('2026-03-31', 4));
    }

    public function testAprilStartFinancialYearOnApril(): void
    {
        $this->assertSame(2026, leave_financial_year('2026-04-01', 4));
    }

    public function testAprilStartBoundsSpanTwoCalendarYears(): void
    {
        [$start, $end] = leave_financial_year_bounds(2026, 4);
        $this->assertSame('2026-04-01', $start);
        $this->assertSame('2027-03-31', $end);
    }

    public function testCalendarYearStartIsPlainYear(): void
    {
        [$start, $end] = leave_financial_year_bounds(2026, 1);
        $this->assertSame('2026-01-01', $start);
        $this->assertSame('2026-12-31', $end);
    }

    /** Edge case explicitly called out in the Phase 18 spec: a Feb-start FY must land on Feb 29 in a leap year, not silently roll to Mar 1. */
    public function testFebruaryStartFinancialYearBoundIncludesLeapDay(): void
    {
        [, $end] = leave_financial_year_bounds(2027, 2);
        // FY2027 (Feb-start) runs 2027-02-01 .. 2028-01-31 — 2028 is a leap year,
        // but the end month is January, so this specific case doesn't touch Feb 29.
        // Test the leap boundary directly instead: an FY that ends in February.
        $this->assertSame('2028-01-31', $end);

        [, $leapEnd] = leave_financial_year_bounds(2027, 3);
        // FY2027 (March-start) runs 2027-03-01 .. 2028-02-29 — 2028 IS a leap year.
        $this->assertSame('2028-02-29', $leapEnd);
    }

    public function testNonLeapYearFebruaryEndBoundIsFeb28(): void
    {
        [, $end] = leave_financial_year_bounds(2026, 3);
        // FY2026 (March-start) runs 2026-03-01 .. 2027-02-28 — 2027 is not a leap year.
        $this->assertSame('2027-02-28', $end);
    }
}
