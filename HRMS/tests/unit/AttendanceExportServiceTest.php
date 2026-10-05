<?php

use App\Services\AttendanceExportService;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Covers the Phase 15 CSV-streaming rewrite (fputcsv into php://temp instead
 * of building a full PhpSpreadsheet object graph) — no DB dependency, this
 * service takes plain arrays.
 *
 * @internal
 */
final class AttendanceExportServiceTest extends CIUnitTestCase
{
    public function testCsvHasHeaderRowFromColumnLabels(): void
    {
        $service = new AttendanceExportService();
        $csv     = $service->toCsv(['emp_code' => 'Employee Code', 'name' => 'Name'], []);

        // PHP 8.1+'s fputcsv quotes a field containing a space next to the escape
        // char by default — real, current behavior, not a bug in the service.
        $this->assertStringStartsWith('"Employee Code",Name', $csv);
    }

    public function testCsvOrdersRowValuesByColumnKeyNotArrayOrder(): void
    {
        $service = new AttendanceExportService();
        // Row's own key order is deliberately scrambled relative to $columns —
        // toCsv() must still emit values in $columns' order, not the row's.
        $csv = $service->toCsv(
            ['emp_code' => 'Employee Code', 'name' => 'Name'],
            [['name' => 'Asha Rao', 'emp_code' => 'EMP000001']]
        );

        $lines = preg_split('/\r\n|\n/', trim($csv));
        $this->assertSame('EMP000001,"Asha Rao"', $lines[1]);
    }

    public function testCsvFillsMissingColumnWithEmptyString(): void
    {
        $service = new AttendanceExportService();
        $csv     = $service->toCsv(['a' => 'A', 'b' => 'B'], [['a' => 'x']]);

        $lines = preg_split('/\r\n|\n/', trim($csv));
        $this->assertSame('x,', $lines[1]);
    }

    public function testCsvEscapesValuesContainingCommas(): void
    {
        $service = new AttendanceExportService();
        $csv     = $service->toCsv(['note' => 'Note'], [['note' => 'Late, but approved']]);

        $this->assertStringContainsString('"Late, but approved"', $csv);
    }
}
