<?php

use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class EmployeeHelperTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        helper('employee');
    }

    public function testMaskAccountNumberKeepsLastFourDigits(): void
    {
        // "123456781234" is 12 characters — 8 masked, last 4 ("1234") left visible.
        $this->assertSame('••••••••1234', mask_account_number('123456781234'));
    }

    public function testMaskAccountNumberFullyMasksShortNumbers(): void
    {
        // A 4-digit-or-shorter number has nothing safe to reveal — mask all of it,
        // not "last 4 digits" (which would be the whole number, defeating the point).
        $this->assertSame('••••', mask_account_number('1234'));
        $this->assertSame('•••', mask_account_number('123'));
    }

    public function testEmployeeStatusLabelHumanizesNoticePeriod(): void
    {
        $this->assertSame('Notice Period', employee_status_label('notice_period'));
        $this->assertSame('Active', employee_status_label('active'));
    }

    public function testEmployeeStatusBadgeClassCoversEveryLifecycleStatus(): void
    {
        $this->assertSame('badge-success', employee_status_badge_class('active'));
        $this->assertSame('badge-danger', employee_status_badge_class('terminated'));
        $this->assertSame('badge-danger', employee_status_badge_class('absconded'));
        $this->assertSame('badge-muted', employee_status_badge_class('relieved'));
    }

    public function testEmployeeInitialsFromFirstAndLastName(): void
    {
        $this->assertSame('JD', employee_initials(['first_name' => 'John', 'last_name' => 'Doe']));
    }

    public function testEmployeeInitialsFallsBackWhenNamesMissing(): void
    {
        $this->assertSame('?', employee_initials([]));
    }
}
