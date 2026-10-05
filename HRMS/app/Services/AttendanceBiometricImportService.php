<?php

namespace App\Services;

use App\Models\AttendanceBiometricLogModel;
use App\Models\AttendanceLogModel;
use App\Models\EmployeeModel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use RuntimeException;

/**
 * The "biometric-ready" architecture: no hardware talks to this app yet.
 * stage() parses an uploaded device export (serial, employee code, punch
 * time, punch type) into the staging table; syncPending() resolves
 * employee_code -> employee_id and replays each row through
 * AttendancePunchService — the exact same path a real GPS punch takes.
 * Ready for a real ZKTeco/eSSL/Matrix export later: only the column
 * mapping in stage() would need to change.
 */
class AttendanceBiometricImportService
{
    private AttendanceBiometricLogModel $logs;

    public function __construct(
        private AuditService $audit = new AuditService(),
        private AttendancePunchService $punch = new AttendancePunchService()
    ) {
        $this->logs = new AttendanceBiometricLogModel(service('tenantContext')->db());
    }

    public function stage(string $absolutePath): array
    {
        $rows = IOFactory::load($absolutePath)->getActiveSheet()->toArray(null, true, false, false);
        if ($rows === []) {
            throw new RuntimeException('The file is empty.');
        }

        array_shift($rows); // header: Device Serial, Employee Code, Punch Time, Punch Type
        $staged = 0;

        foreach ($rows as $row) {
            if (implode('', array_map('strval', $row)) === '') {
                continue;
            }

            $code = trim((string) ($row[1] ?? ''));
            $time = $this->parseDateTime($row[2] ?? null);
            if ($code === '' || ! $time) {
                continue;
            }

            $type = strtolower(trim((string) ($row[3] ?? '')));
            $type = in_array($type, ['in', 'out'], true) ? $type : null;

            $this->logs->insert([
                'device_serial' => trim((string) ($row[0] ?? '')), 'employee_code' => $code, 'punch_time' => $time, 'punch_type' => $type,
                'raw_payload' => json_encode($row), 'sync_status' => 'pending', 'created_at' => date('Y-m-d H:i:s'),
            ]);
            $staged++;
        }

        return ['staged' => $staged];
    }

    /** @return array{synced:int, failed:int} */
    public function syncPending(): array
    {
        $db        = service('tenantContext')->db();
        $codeToId  = array_column((new EmployeeModel($db))->select('id, employee_code')->findAll(), 'id', 'employee_code');
        $synced    = 0;
        $failed    = 0;

        foreach ($this->logs->pending() as $row) {
            $employeeId = $codeToId[$row['employee_code']] ?? null;

            if (! $employeeId) {
                $this->logs->update($row['id'], ['sync_status' => 'failed']);
                $failed++;
                continue;
            }

            $type = $row['punch_type'];
            if (! $type) {
                // Some devices don't report direction — infer it from whether a punch-in is already open.
                $hasOpen = (new AttendanceLogModel($db))->hasOpenPunchIn($employeeId);
                $type    = $hasOpen ? 'out' : 'in';
            }

            try {
                $this->punch->punch($employeeId, $type, [], 'biometric', $row['punch_time']);
                $this->logs->update($row['id'], ['sync_status' => 'synced', 'synced_employee_id' => $employeeId]);
                $synced++;
            } catch (RuntimeException) {
                $this->logs->update($row['id'], ['sync_status' => 'failed']);
                $failed++;
            }
        }

        $this->audit->log('biometric_sync', 'attendance', 'attendance_biometric_log', null, null, ['synced' => $synced, 'failed' => $failed]);

        return ['synced' => $synced, 'failed' => $failed];
    }

    private function parseDateTime(mixed $raw): ?string
    {
        if ($raw === null || $raw === '') {
            return null;
        }
        if (is_numeric($raw)) {
            try {
                return ExcelDate::excelToDateTimeObject((float) $raw)->format('Y-m-d H:i:s');
            } catch (\Throwable) {
                return null;
            }
        }
        $timestamp = strtotime((string) $raw);

        return $timestamp !== false ? date('Y-m-d H:i:s', $timestamp) : null;
    }
}
