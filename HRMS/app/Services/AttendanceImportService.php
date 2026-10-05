<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use RuntimeException;

/**
 * Two-step import, same shape as EmployeeImportService: parse()+validate() build a
 * preview (no DB writes); commit() — a separate request, after the user confirms —
 * writes through AttendanceService::markManual(), the exact same upsert+audit path
 * the Edit and Bulk Mark Attendance pages already use, so an Excel-sourced row never
 * bypasses the business rules those pages enforce.
 */
class AttendanceImportService
{
    private const HEADERS = ['Employee Code', 'Attendance Date', 'In Time', 'Out Time', 'Status'];

    /** Only the statuses AttendanceController::bulkForm lets HR assert by hand — the rest (leave, late, missed_punch, ...) are system-computed. */
    private const VALID_STATUSES = ['present', 'absent', 'half_day', 'holiday', 'work_from_home', 'on_duty'];

    public function template(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Attendance');
        $sheet->fromArray(self::HEADERS, null, 'A1');
        $sheet->fromArray(['EMP000007', '30-09-2026', '09:30', '18:30', 'Present'], null, 'A2');
        $sheet->fromArray(['EMP000012', '30-09-2026', '09:45', '18:15', 'Present'], null, 'A3');
        $sheet->fromArray(['EMP000021', '30-09-2026', '', '', 'Absent'], null, 'A4');
        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $instructions = $spreadsheet->createSheet();
        $instructions->setTitle('Instructions');
        $instructions->fromArray([
            ['Column', 'Required', 'Notes'],
            ['Employee Code', 'Yes', 'Must match an existing employee\'s code exactly.'],
            ['Attendance Date', 'Yes', 'Format: DD-MM-YYYY.'],
            ['In Time', 'No', 'Format: HH:MM (24-hour). Leave blank for Absent/Holiday etc.'],
            ['Out Time', 'No', 'Format: HH:MM (24-hour). Must not be earlier than In Time.'],
            ['Status', 'Yes', 'One of: ' . implode(', ', array_map(static fn ($s) => ucwords(str_replace('_', ' ', $s)), self::VALID_STATUSES)) . '.'],
            [''],
            ['Duplicate handling', '', 'One Employee Code + Attendance Date pair per row. If the same pair appears twice in this file, only the first is imported. If attendance already exists in the system for that employee/date, it is updated, not duplicated.'],
            ['Empty rows', '', 'Skipped automatically.'],
        ], null, 'A1');
        $instructions->getColumnDimension('A')->setWidth(20);
        $instructions->getColumnDimension('C')->setWidth(80);

        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }

    /** @return array<int, array<string, mixed>> raw rows keyed by header name, keyed by their 1-indexed spreadsheet row number */
    public function parse(string $absolutePath): array
    {
        $sheet = IOFactory::load($absolutePath)->getActiveSheet();
        $rows  = $sheet->toArray(null, true, false, false);

        if ($rows === []) {
            throw new RuntimeException('The file is empty.');
        }

        $header = array_map(static fn ($h) => trim((string) $h), array_shift($rows));
        $out    = [];

        foreach ($rows as $i => $row) {
            if (implode('', array_map('strval', $row)) === '') {
                continue;
            }
            $out[$i + 2] = array_combine($header, array_pad($row, count($header), null));
        }

        return $out;
    }

    /** @return array{rows: array<int, array{row:int, data:array, errors:array, result:string}>, validCount:int, errorCount:int, existingCount:int, duplicateCount:int} */
    public function validate(array $rows): array
    {
        $db = service('tenantContext')->db();

        // Excludes 'terminated' employees, same rule AttendanceController::bulkMark applies
        // when resolving who a bulk selection ('all active employees' etc.) actually covers.
        $codeToId = array_column(
            $db->table('employees')->select('id, employee_code')->where('deleted_at', null)->where('status !=', 'terminated')->get()->getResultArray(),
            'id',
            'employee_code'
        );
        $existingAttendance = [];
        foreach ($db->table('attendance')->select('employee_id, attendance_date')->where('deleted_at', null)->get()->getResultArray() as $r) {
            $existingAttendance[$r['employee_id'] . '|' . $r['attendance_date']] = true;
        }

        $out            = [];
        $validCount     = 0;
        $errorCount     = 0;
        $existingCount  = 0;
        $duplicateCount = 0;
        $seenInFile     = [];

        foreach ($rows as $rowNum => $row) {
            $errors = [];

            $code       = trim((string) ($row['Employee Code'] ?? ''));
            $employeeId = $codeToId[$code] ?? null;
            if ($code === '') {
                $errors[] = 'Employee Code is required.';
            } elseif (! $employeeId) {
                $errors[] = "Employee Code '{$code}' not found.";
            }

            $date = $this->parseDate($row['Attendance Date'] ?? null);
            if (! $date) {
                $errors[] = 'Attendance Date is missing or unrecognized (use DD-MM-YYYY).';
            }

            $inTime  = $this->parseTime($row['In Time'] ?? null);
            $outTime = $this->parseTime($row['Out Time'] ?? null);
            if (($row['In Time'] ?? '') !== '' && $inTime === false) {
                $errors[] = 'In Time is not a valid time (use HH:MM).';
                $inTime   = null;
            }
            if (($row['Out Time'] ?? '') !== '' && $outTime === false) {
                $errors[] = 'Out Time is not a valid time (use HH:MM).';
                $outTime  = null;
            }
            if ($inTime && $outTime && $outTime <= $inTime) {
                $errors[] = 'Out Time must be after In Time.';
            }

            $statusRaw = trim((string) ($row['Status'] ?? ''));
            $status    = str_replace(' ', '_', strtolower($statusRaw));
            if ($statusRaw === '' || ! in_array($status, self::VALID_STATUSES, true)) {
                $errors[] = $statusRaw === '' ? 'Status is required.' : "Status '{$statusRaw}' is not recognized.";
                $status   = null;
            }

            $result = 'Valid';
            $isDuplicateInFile = false;
            if ($employeeId && $date) {
                $key = $employeeId . '|' . $date;
                if (isset($seenInFile[$key])) {
                    $isDuplicateInFile = true;
                    $errors[]          = 'Duplicate Employee Code + Attendance Date within this file.';
                }
                $seenInFile[$key] = true;
            }

            if ($errors !== []) {
                $result = 'Invalid';
                $errorCount++;
            } elseif (isset($existingAttendance[$employeeId . '|' . $date])) {
                $result = 'Existing (will update)';
                $existingCount++;
                $validCount++;
            } else {
                $validCount++;
            }
            $isDuplicateInFile && $duplicateCount++;

            $out[] = [
                'row'    => $rowNum,
                'data'   => [
                    'employee_code' => $code,
                    'employee_id'   => $employeeId,
                    'date'          => $date,
                    'in_time'       => $inTime ?: null,
                    'out_time'      => $outTime ?: null,
                    'status'        => $status,
                ],
                'errors' => $errors,
                'result' => $result,
            ];
        }

        return [
            'rows'          => $out,
            'validCount'    => $validCount,
            'errorCount'    => $errorCount,
            'existingCount' => $existingCount,
            'duplicateCount'=> $duplicateCount,
        ];
    }

    /**
     * @param array<int, array{row:int, data:array, errors:array, result:string}> $rows exactly what validate() produced
     * @return array{imported:int, skipped:int}
     */
    public function commit(array $rows): array
    {
        $db      = service('tenantContext')->db();
        $service = new AttendanceService();
        $userId  = (int) session('tenant_user_id');

        $imported = 0;
        $skipped  = 0;

        $db->transStart();

        try {
            foreach ($rows as $row) {
                if ($row['errors'] !== []) {
                    $skipped++;
                    continue;
                }

                $d = $row['data'];
                $service->markManual((int) $d['employee_id'], $d['date'], $d['status'], $userId, $d['in_time'], $d['out_time']);
                $imported++;
            }
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'Attendance Excel import failed: ' . $e->getMessage());

            throw new RuntimeException('Import failed and no records were saved. Please try again.');
        }

        $db->transComplete();
        if ($db->transStatus() === false) {
            throw new RuntimeException('Import failed and no records were saved. Please try again.');
        }

        return ['imported' => $imported, 'skipped' => $skipped];
    }

    private function parseDate(mixed $raw): ?string
    {
        if ($raw === null || $raw === '') {
            return null;
        }

        if (is_numeric($raw)) {
            try {
                return ExcelDate::excelToDateTimeObject((float) $raw)->format('Y-m-d');
            } catch (\Throwable) {
                return null;
            }
        }

        $raw = trim((string) $raw);
        foreach (['d-m-Y', 'd/m/Y', 'Y-m-d'] as $format) {
            $dt = \DateTime::createFromFormat($format, $raw);
            if ($dt && $dt->format($format) === $raw) {
                return $dt->format('Y-m-d');
            }
        }

        return null;
    }

    /** @return string|null|false 'H:i' on success, null when blank, false when unparseable */
    private function parseTime(mixed $raw): string|null|false
    {
        if ($raw === null || $raw === '') {
            return null;
        }

        if (is_numeric($raw)) {
            try {
                return ExcelDate::excelToDateTimeObject((float) $raw)->format('H:i');
            } catch (\Throwable) {
                return false;
            }
        }

        $raw = trim((string) $raw);
        foreach (['H:i', 'H:i:s', 'g:i A'] as $format) {
            $dt = \DateTime::createFromFormat($format, $raw);
            if ($dt !== false) {
                return $dt->format('H:i');
            }
        }

        return false;
    }
}
