<?php

namespace App\Services;

use App\Models\AttendanceLogModel;
use App\Models\AttendanceSettingModel;
use App\Models\EmployeeModel;
use RuntimeException;

/**
 * The punch orchestrator: validates (one open punch-in per day, punch-out
 * requires an open punch-in), resolves geofence status, auto-registers the
 * device, writes the raw log, then hands off to AttendanceSummaryService to
 * recompute the day's aggregate. This is the single entry point every punch
 * — GPS, manual correction, or biometric sync — ultimately goes through.
 */
class AttendancePunchService
{
    /**
     * Minimum gap enforced between two self-service (GPS) punches for the
     * same employee — a fast double-click, an impatient double-tap on a
     * slow connection, or a retried request otherwise produces a punch-in
     * immediately followed by a punch-out a few seconds later, which is
     * never a real attendance event. Biometric imports and manual/HR
     * corrections intentionally bypass this (see the `source === 'gps'`
     * guard below) since those can legitimately backfill closely-spaced
     * historical rows.
     */
    private const MIN_GPS_PUNCH_INTERVAL_SECONDS = 60;

    public function __construct(
        private GeofenceService $geofence = new GeofenceService(),
        private AttendanceDeviceService $devices = new AttendanceDeviceService(),
        private AttendanceSummaryService $summary = new AttendanceSummaryService(),
        private AuditService $audit = new AuditService()
    ) {
    }

    /**
     * @param array{lat?:?float,lng?:?float,accuracy?:?float,deviceUid?:?string,deviceName?:?string} $context
     * @param string $source 'gps'|'manual'|'biometric'
     */
    public function punch(int $employeeId, string $punchType, array $context = [], string $source = 'gps', ?string $atTime = null): array
    {
        if (! in_array($punchType, ['in', 'out'], true)) {
            throw new RuntimeException('Invalid punch type.');
        }

        $db       = service('tenantContext')->db();
        $employee = (new EmployeeModel($db))->find($employeeId);
        if (! $employee) {
            throw new RuntimeException('Employee not found.');
        }

        $when     = $atTime ?? date('Y-m-d H:i:s');
        $logModel = new AttendanceLogModel($db);

        // The open-punch check and the insert must be atomic: two near-simultaneous punches
        // (a double-click, a flaky-network retry, the same account open in two tabs/devices)
        // could otherwise both read "no open punch-in" before either commits, producing two
        // 'in' rows for the same day. FOR UPDATE locks the employee's latest log row for the
        // duration of this transaction so the second request blocks until the first commits,
        // then re-reads the now-current state.
        $db->transStart();

        try {
            $lastPunch = $logModel->mostRecentPunchForUpdate($employeeId);
            $hasOpen   = $lastPunch !== null && $lastPunch['punch_type'] === 'in';

            if ($punchType === 'in' && $hasOpen) {
                throw new RuntimeException('Already punched in — punch out first.');
            }
            if ($punchType === 'out' && ! $hasOpen) {
                throw new RuntimeException('No open punch-in found for today.');
            }

            if ($source === 'gps' && $lastPunch !== null) {
                $secondsSinceLast = strtotime($when) - strtotime($lastPunch['punch_time']);
                if ($secondsSinceLast < self::MIN_GPS_PUNCH_INTERVAL_SECONDS) {
                    throw new RuntimeException('You already punched ' . $lastPunch['punch_type'] . ' moments ago — please wait a minute before punching again.');
                }
            }

            // A punch-out closing an open punch-in belongs to that punch-in's shift date
            // (e.g. a night shift punch-in at 9:45 PM closed by a 6:15 AM punch-out the
            // next morning must both recompute the same attendance date — the shift's
            // start date, not the punch-out's own calendar day).
            $attendanceDate = ($punchType === 'out' && $hasOpen)
                ? date('Y-m-d', strtotime($lastPunch['punch_time']))
                : date('Y-m-d', strtotime($when));

            $settings = (new AttendanceSettingModel($db))->current();
            $lat      = $context['lat'] ?? null;
            $lng      = $context['lng'] ?? null;
            $accuracy = $context['accuracy'] ?? null;

            $geo = $source === 'gps'
                ? $this->geofence->resolve($lat, $lng, $accuracy, (int) $employee['branch_id'])
                : ['status' => 'not_applicable', 'distanceMeters' => null, 'locationId' => null];

            if ($source === 'gps' && ! empty($settings['gps_required'])) {
                if ($geo['status'] === 'outside') {
                    throw new RuntimeException("Attendance not allowed — you are outside the office location ({$geo['distanceMeters']}m away). Please punch from the office.");
                }
                if ($geo['status'] === 'gps_disabled') {
                    throw new RuntimeException('Location access is required to punch — please enable GPS and try again.');
                }
            }

            $device = null;
            if ($source === 'gps' && ! empty($context['deviceUid'])) {
                $device = $this->devices->registerOrTouch($employeeId, $context['deviceUid'], $context['deviceName'] ?? null);
            }

            $logId = $logModel->insert([
                'employee_id'     => $employeeId,
                'punch_type'      => $punchType,
                'punch_time'      => $when,
                'latitude'        => $lat,
                'longitude'       => $lng,
                'accuracy_meters' => $accuracy,
                'device_id'       => $device['id'] ?? null,
                'location_id'     => $geo['locationId'],
                'distance_meters' => $geo['distanceMeters'],
                'geofence_status' => $geo['status'],
                'ip_address'      => service('request')->getIPAddress(),
                'source'          => $source,
                'created_at'      => date('Y-m-d H:i:s'),
            ], true);
        } catch (RuntimeException $e) {
            $db->transRollback();

            throw $e;
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            throw new RuntimeException('Could not record punch — please try again.');
        }

        $attendance = $this->summary->recompute($employeeId, $attendanceDate);

        $this->audit->log('punch_' . $punchType, 'attendance', 'attendance_log', $logId, null, [
            'employee_id' => $employeeId, 'geofence_status' => $geo['status'], 'distance_meters' => $geo['distanceMeters'], 'source' => $source,
        ]);

        return ['log_id' => $logId, 'geofence' => $geo, 'attendance' => $attendance];
    }
}
