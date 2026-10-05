<?php

namespace App\Services;

use App\Models\AttendanceHolidayModel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use RuntimeException;

class AttendanceHolidayService
{
    private AttendanceHolidayModel $holidays;

    public function __construct(
        private AuditService $audit = new AuditService(),
        private LookupCacheService $cache = new LookupCacheService()
    ) {
        $this->holidays = new AttendanceHolidayModel(service('tenantContext')->db());
    }

    public function create(array $data): int
    {
        $this->assertNotDuplicate($data['date'], $data['branch_id'] ?? null, null);

        $id = $this->holidays->insert($data);
        $this->audit->log('create', 'attendance', 'attendance_holiday', $id, null, $data);
        $this->cache->invalidate('holidays');

        return $id;
    }

    public function update(int $id, array $data): void
    {
        $old = $this->holidays->find($id);
        if (! $old) {
            throw new RuntimeException('Holiday not found.');
        }

        $this->assertNotDuplicate($data['date'], $data['branch_id'] ?? null, $id);

        $this->holidays->update($id, $data);
        $this->audit->log('update', 'attendance', 'attendance_holiday', $id, $old, $data);
        $this->cache->invalidate('holidays');
    }

    /** Same date + same scope (a specific branch, or company-wide) already has a holiday on file — a plain duplicate, not a branch-specific-vs-company-wide overlap (which forDate() already resolves on purpose). */
    private function assertNotDuplicate(string $date, ?int $branchId, ?int $ignoreId): void
    {
        $query = $this->holidays->where('date', $date)->where('status', 'active');
        $query = $branchId === null ? $query->where('branch_id', null) : $query->where('branch_id', $branchId);
        if ($ignoreId !== null) {
            $query->where('id !=', $ignoreId);
        }

        if ($query->countAllResults() > 0) {
            throw new RuntimeException('A holiday is already scheduled for this date' . ($branchId ? ' in this branch' : ' company-wide') . '.');
        }
    }

    /**
     * Builds the "Generate Next Year" preview: for every active holiday in
     * $sourceYear, proposes what its $sourceYear+1 record would look like.
     * Nothing is written here — this only computes the preview rows; the
     * admin reviews/edits them and generateNextYear() does the actual insert.
     *
     * @return array{source_year:int, target_year:int, rows:array}
     */
    public function proposeNextYear(int $sourceYear): array
    {
        $targetYear = $sourceYear + 1;

        $source = $this->holidays
            ->select('attendance_holidays.*, b.name as branch_name')
            ->join('branches b', 'b.id = attendance_holidays.branch_id', 'left')
            ->forYear($sourceYear);

        $existingTarget = $this->holidays->forYear($targetYear);
        $existingKeys = array_map(
            static fn (array $h) => strtolower(trim($h['name'])) . '|' . ($h['branch_id'] ?? 'all'),
            $existingTarget
        );

        $rows = [];
        $dateBuckets = [];

        foreach ($source as $h) {
            [$y, $m, $d] = explode('-', $h['date']);
            $isFeb29 = $m === '02' && $d === '29';
            $targetIsLeap = checkdate(2, 29, $targetYear);

            $proposedDate = null;
            $needsReview  = ! (bool) $h['is_annual'];
            $note = null;

            if ($isFeb29 && ! $targetIsLeap) {
                // Never silently pick Feb 28 or Mar 1 on the holiday's behalf — that's
                // a real calendar decision for whoever manages the holiday list.
                $needsReview = true;
                $note = $targetYear . ' is not a leap year — Feb 29 has no matching date. Choose one manually (e.g. Feb 28 or Mar 1).';
            } elseif ($h['is_annual']) {
                $proposedDate = sprintf('%04d-%02d-%02d', $targetYear, (int) $m, (int) $d);
            }
            // Movable (is_annual = 0) and not a Feb 29 edge case: leave proposedDate
            // null on purpose — "must NOT automatically be treated as correct."

            $key = strtolower(trim($h['name'])) . '|' . ($h['branch_id'] ?? 'all');
            $alreadyExists = in_array($key, $existingKeys, true);

            if ($proposedDate !== null) {
                $bucketKey = $proposedDate . '|' . ($h['branch_id'] ?? 'all');
                $dateBuckets[$bucketKey][] = $h['name'];
            }

            $rows[] = [
                'source_id'      => $h['id'],
                'name'           => $h['name'],
                'source_date'    => $h['date'],
                'proposed_date'  => $proposedDate,
                'is_annual'      => (bool) $h['is_annual'],
                'needs_review'   => $needsReview,
                'already_exists' => $alreadyExists,
                'holiday_type'   => $h['holiday_type'],
                'branch_id'      => $h['branch_id'],
                'branch_name'    => $h['branch_name'] ?? 'All branches',
                'is_optional'    => (int) $h['is_optional'],
                'description'    => $h['description'],
                'note'           => $note,
                'has_conflict'   => false,
            ];
        }

        foreach ($rows as &$row) {
            if ($row['proposed_date'] === null) {
                continue;
            }
            $bucketKey = $row['proposed_date'] . '|' . ($row['branch_id'] ?? 'all');
            $row['has_conflict'] = count($dateBuckets[$bucketKey] ?? []) > 1;
        }
        unset($row);

        return ['source_year' => $sourceYear, 'target_year' => $targetYear, 'rows' => $rows];
    }

    /**
     * Creates the admin-confirmed rows from the "Generate Next Year" preview.
     * Each $item is one row the admin chose to include, already carrying its
     * final (possibly hand-edited) date. Duplicates (same date+branch scope,
     * or the exact holiday already present in the target year) are skipped,
     * not treated as failures — the whole batch runs inside one transaction
     * so a genuine failure never leaves a partially-generated year behind.
     *
     * @param array<int, array{name:string,date:string,holiday_type:string,branch_id:?int,description:?string,is_optional:int,is_annual:int}> $items
     * @return array{created:int, skipped:int, errors:string[], target_year:int}
     */
    public function generateNextYear(int $sourceYear, array $items): array
    {
        $targetYear = $sourceYear + 1;
        $created = 0;
        $skipped = 0;
        $errors  = [];

        $db = service('tenantContext')->db();
        $db->transStart();

        foreach ($items as $item) {
            $name = trim((string) ($item['name'] ?? ''));
            $date = (string) ($item['date'] ?? '');
            $ts   = $date !== '' ? strtotime($date) : false;

            if ($name === '' || $ts === false) {
                $errors[] = ($name !== '' ? $name : 'Row') . ': a valid date is required.';
                $skipped++;
                continue;
            }
            if ((int) date('Y', $ts) !== $targetYear) {
                $errors[] = $name . ': date must fall within ' . $targetYear . '.';
                $skipped++;
                continue;
            }

            try {
                $this->create([
                    'name'         => $name,
                    'date'         => $date,
                    'holiday_type' => in_array($item['holiday_type'] ?? 'public', ['public', 'restricted', 'company'], true) ? $item['holiday_type'] : 'public',
                    'branch_id'    => $item['branch_id'] ?? null,
                    'description'  => $item['description'] ?? null,
                    'is_optional'  => ! empty($item['is_optional']) ? 1 : 0,
                    'is_annual'    => ! empty($item['is_annual']) ? 1 : 0,
                    'status'       => 'active',
                ]);
                $created++;
            } catch (RuntimeException $e) {
                // Already exists for that date+branch scope — idempotent skip, not a failure.
                $skipped++;
                $errors[] = $name . ': ' . $e->getMessage();
            }
        }

        $db->transComplete();
        if ($db->transStatus() === false) {
            throw new RuntimeException('Holiday generation failed and was rolled back — no holidays for ' . $targetYear . ' were saved.');
        }

        $this->audit->log(
            'generate',
            'attendance',
            'attendance_holiday_batch',
            $targetYear,
            ['source_year' => $sourceYear],
            ['target_year' => $targetYear, 'created' => $created, 'skipped' => $skipped]
        );
        $this->cache->invalidate('holidays');

        return ['created' => $created, 'skipped' => $skipped, 'errors' => $errors, 'target_year' => $targetYear];
    }

    public function delete(int $id): void
    {
        $old = $this->holidays->find($id);
        if (! $old) {
            throw new RuntimeException('Holiday not found.');
        }

        $this->holidays->delete($id);
        $this->audit->log('delete', 'attendance', 'attendance_holiday', $id, $old, null);
        $this->cache->invalidate('holidays');
    }

    public function template(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->fromArray(['Holiday Name', 'Date', 'Type', 'Optional (Y/N)', 'Description'], null, 'A1');
        $sheet->fromArray(['Republic Day', '2027-01-26', 'public', 'N', 'National holiday'], null, 'A2');
        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return $spreadsheet;
    }

    /** @return array{imported:int, skipped:int, errors:string[]} */
    public function import(string $absolutePath): array
    {
        $rows = IOFactory::load($absolutePath)->getActiveSheet()->toArray(null, true, false, false);
        if ($rows === []) {
            throw new RuntimeException('The file is empty.');
        }

        array_shift($rows); // header
        $imported = 0;
        $skipped  = 0;
        $errors   = [];

        foreach ($rows as $i => $row) {
            if (implode('', array_map('strval', $row)) === '') {
                continue;
            }

            $name = trim((string) ($row[0] ?? ''));
            $date = $this->parseDate($row[1] ?? null);
            $type = strtolower(trim((string) ($row[2] ?? 'public')));
            $type = in_array($type, ['public', 'restricted', 'company'], true) ? $type : 'public';
            $optional = strtoupper(trim((string) ($row[3] ?? 'N'))) === 'Y';
            $description = trim((string) ($row[4] ?? '')) ?: null;

            if ($name === '' || ! $date) {
                $errors[] = 'Row ' . ($i + 2) . ': name and a valid date are required.';
                $skipped++;
                continue;
            }

            try {
                $this->create([
                    'name' => $name, 'date' => $date, 'holiday_type' => $type,
                    'is_optional' => $optional ? 1 : 0, 'description' => $description, 'branch_id' => null, 'status' => 'active',
                ]);
                $imported++;
            } catch (RuntimeException $e) {
                $errors[] = 'Row ' . ($i + 2) . ': ' . $e->getMessage();
                $skipped++;
            }
        }

        return ['imported' => $imported, 'skipped' => $skipped, 'errors' => $errors];
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
}
