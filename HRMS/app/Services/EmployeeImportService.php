<?php

namespace App\Services;

use App\Models\EmployeeModel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use RuntimeException;

/**
 * Two-step import: parse()+validate() build a preview (no DB writes) that
 * the browser shows back to the user; commit() — a separate request, after
 * the user confirms — does the real inserts. Every row is judged
 * independently, so one bad row never blocks the rest of the file.
 */
class EmployeeImportService
{
    private const HEADERS = [
        'Employee Code', 'Name', 'Branch', 'Department', 'Designation', 'Manager', 'Email', 'Mobile', 'Joining Date', 'Status',
    ];

    private const VALID_STATUSES = [
        'active', 'probation', 'notice_period', 'suspended', 'resigned', 'terminated', 'retired', 'absconded', 'relieved',
    ];

    public function template(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->fromArray(self::HEADERS, null, 'A1');
        $sheet->fromArray(
            ['', 'Jane Doe', 'Head Office', 'Engineering', 'Software Engineer', 'EMP000001', 'jane.doe@example.com', '9876543210', '2026-01-15', 'probation'],
            null,
            'A2'
        );
        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

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

    /** @return array{rows: array<int, array{row:int, data:array, errors:array, isDuplicate:bool}>, validCount:int, errorCount:int, duplicateCount:int} */
    public function validate(array $rows): array
    {
        $db            = service('tenantContext')->db();
        $branches      = $this->lookupByName($db, 'branches');
        $departments   = $this->lookupByName($db, 'departments');
        $designations  = $this->lookupByName($db, 'designations');
        $existingCodes = array_map('strtoupper', array_column(
            $db->table('employees')->select('employee_code')->get()->getResultArray(), 'employee_code'
        ));

        $out            = [];
        $validCount     = 0;
        $errorCount     = 0;
        $duplicateCount = 0;
        $seenInFile     = [];

        foreach ($rows as $rowNum => $row) {
            $errors = [];

            $name              = trim((string) ($row['Name'] ?? ''));
            [$firstName, $lastName] = $this->splitName($name);
            if ($name === '') {
                $errors[] = 'Name is required.';
            }

            $branchName = trim((string) ($row['Branch'] ?? ''));
            $branchId   = $branches[strtolower($branchName)] ?? null;
            if ($branchName === '') {
                $errors[] = 'Branch is required.';
            } elseif (! $branchId) {
                $errors[] = "Branch '{$branchName}' not found.";
            }

            $deptName = trim((string) ($row['Department'] ?? ''));
            $deptId   = $departments[strtolower($deptName)] ?? null;
            if ($deptName === '') {
                $errors[] = 'Department is required.';
            } elseif (! $deptId) {
                $errors[] = "Department '{$deptName}' not found.";
            }

            $desigName = trim((string) ($row['Designation'] ?? ''));
            $desigId   = $designations[strtolower($desigName)] ?? null;
            if ($desigName === '') {
                $errors[] = 'Designation is required.';
            } elseif (! $desigId) {
                $errors[] = "Designation '{$desigName}' not found.";
            }

            $mobile = preg_replace('/\D/', '', (string) ($row['Mobile'] ?? ''));
            if (! preg_match('/^[0-9]{10}$/', $mobile)) {
                $errors[] = 'Mobile must be a 10-digit number.';
            }

            $email = trim((string) ($row['Email'] ?? ''));
            if ($email !== '' && ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Email is not valid.';
            }

            $joiningDate = $this->parseDate($row['Joining Date'] ?? null);
            if (! $joiningDate) {
                $errors[] = 'Joining Date is missing or unrecognized (use YYYY-MM-DD).';
            }

            $status = str_replace(' ', '_', strtolower(trim((string) ($row['Status'] ?? '')))) ?: 'probation';
            if (! in_array($status, self::VALID_STATUSES, true)) {
                $errors[] = "Status '{$row['Status']}' is not recognized.";
                $status   = 'probation';
            }

            $code        = trim((string) ($row['Employee Code'] ?? ''));
            $isDuplicate = false;
            if ($code !== '') {
                $codeUpper   = strtoupper($code);
                $isDuplicate = in_array($codeUpper, $existingCodes, true) || isset($seenInFile[$codeUpper]);
                $seenInFile[$codeUpper] = true;
            }

            $data = [
                'employee_code'   => $code ?: null,
                'first_name'      => $firstName,
                'last_name'       => $lastName,
                'branch_id'       => $branchId,
                'branch_name'     => $branchName,
                'department_id'   => $deptId,
                'department_name' => $deptName,
                'designation_id'  => $desigId,
                'designation_name'=> $desigName,
                'manager_ref'     => trim((string) ($row['Manager'] ?? '')) ?: null,
                'company_email'   => $email ?: null,
                'mobile'          => $mobile,
                'date_of_joining' => $joiningDate,
                'status'          => $status,
            ];

            $errors === [] ? $validCount++ : $errorCount++;
            $isDuplicate && $duplicateCount++;

            $out[] = ['row' => $rowNum, 'data' => $data, 'errors' => $errors, 'isDuplicate' => $isDuplicate];
        }

        return ['rows' => $out, 'validCount' => $validCount, 'errorCount' => $errorCount, 'duplicateCount' => $duplicateCount];
    }

    /**
     * @param array<int, array{row:int, data:array, errors:array, isDuplicate:bool}> $rows exactly what validate() produced
     * @return array{inserted:int, skipped:int}
     */
    public function commit(array $rows, bool $skipDuplicates): array
    {
        $duplicatesPresent = (bool) array_filter($rows, static fn ($r) => $r['isDuplicate'] && $r['errors'] === []);
        if ($duplicatesPresent && ! $skipDuplicates) {
            throw new RuntimeException('This file has duplicate employee codes. Enable "Skip duplicates" or fix the file and re-upload.');
        }

        $db              = service('tenantContext')->db();
        $employeeModel   = new EmployeeModel($db);
        $employeeService = new EmployeeService();

        $inserted = 0;
        $skipped  = 0;

        foreach ($rows as $row) {
            if ($row['errors'] !== [] || ($row['isDuplicate'] && $skipDuplicates)) {
                $skipped++;
                continue;
            }

            $data      = $row['data'];
            $managerId = ! empty($data['manager_ref']) ? $this->resolveManager($db, $data['manager_ref']) : null;
            unset($data['manager_ref']);
            $data['reporting_manager_id'] = $managerId;

            if (empty($data['employee_code'])) {
                unset($data['employee_code']);
                $employeeService->create($data);
            } else {
                // Pre-supplied code (data-migration scenario) — insert directly, bypassing the
                // sequence generator, which only needs to stay ahead of *generated* codes.
                $data['created_by'] = session('tenant_user_id');
                $employeeModel->insert($data);
            }

            $inserted++;
        }

        return ['inserted' => $inserted, 'skipped' => $skipped];
    }

    private function splitName(string $name): array
    {
        $parts = preg_split('/\s+/', $name, 2);

        return [$parts[0] ?? '', $parts[1] ?? ($parts[0] ?? '')];
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

        $timestamp = strtotime((string) $raw);

        return $timestamp !== false ? date('Y-m-d', $timestamp) : null;
    }

    /** @return array<string, int> lowercased name => id */
    private function lookupByName(\CodeIgniter\Database\BaseConnection $db, string $table): array
    {
        $rows = $db->table($table)->select('id, name')->where('deleted_at', null)->get()->getResultArray();
        $map  = [];
        foreach ($rows as $row) {
            $map[strtolower($row['name'])] = (int) $row['id'];
        }

        return $map;
    }

    private function resolveManager(\CodeIgniter\Database\BaseConnection $db, string $ref): ?int
    {
        $byCode = $db->table('employees')->select('id')->where('employee_code', $ref)->where('deleted_at', null)->get()->getRowArray();
        if ($byCode) {
            return (int) $byCode['id'];
        }

        $byName = $db->table('employees')
            ->select('id')
            ->where("CONCAT(first_name, ' ', last_name) =", $ref)
            ->where('deleted_at', null)
            ->get()
            ->getRowArray();

        return $byName ? (int) $byName['id'] : null;
    }
}
