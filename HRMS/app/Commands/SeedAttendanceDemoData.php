<?php

namespace App\Commands;

use App\Models\AttendanceDeviceModel;
use App\Models\AttendanceHolidayModel;
use App\Models\AttendanceLocationModel;
use App\Models\AttendanceModel;
use App\Models\AttendanceShiftAssignmentModel;
use App\Models\AttendanceShiftModel;
use App\Models\AttendanceWeeklyOffModel;
use App\Models\BranchModel;
use App\Models\CompanySettingModel;
use App\Models\EmployeeModel;
use App\Services\TenantConnectionFactory;
use App\Services\TenantProvisioningService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Database\BaseConnection;

/**
 * Dev/test-only helper, same shape as Phase 4's SeedEmployeeDemoData: migrates
 * a tenant to pick up the Attendance schema, re-seeds RBAC, then seeds
 * shifts/locations/holidays/weekly-offs/devices/backfilled attendance.
 *
 * Runs outside an HTTP request, so — like SeedEmployeeDemoData — it never
 * touches service('tenantContext')->db()/tenant()/session(); everything works directly off the
 * $db connection resolved from the platform database by company code.
 * Requires employees to already exist (run employee:seed-demo first).
 */
class SeedAttendanceDemoData extends BaseCommand
{
    protected $group       = 'Attendance';
    protected $name        = 'attendance:seed-demo';
    protected $description = 'Migrates + seeds demo attendance data into one tenant, by company code.';
    protected $usage       = 'attendance:seed-demo <company_code>';
    protected $arguments   = ['company_code' => 'The companies.code value, e.g. abc'];

    public function run(array $params)
    {
        $code = $params[0] ?? CLI::prompt('Company code');
        if (! $code) {
            CLI::error('Company code is required.');

            return;
        }

        $platform = db_connect('master');
        $row      = $platform->table('companies c')
            ->select('c.id as company_id, c.code, cdc.db_host, cdc.db_port, cdc.db_name, cdc.db_username, cdc.db_password_enc, cdc.status as conn_status')
            ->join('company_database_connections cdc', 'cdc.company_id = c.id')
            ->where('c.code', $code)
            ->get()
            ->getRowArray();

        if (! $row || $row['conn_status'] !== 'provisioned') {
            CLI::error("No provisioned company found with code '{$code}'.");

            return;
        }

        $password = service('encrypter')->decrypt(base64_decode((string) $row['db_password_enc']));
        $db       = (new TenantConnectionFactory())->build($row['db_host'], (int) $row['db_port'], $row['db_name'], $row['db_username'], $password);

        CLI::write("Migrating {$row['db_name']}...", 'yellow');
        (new TenantProvisioningService())->runMigrations($db);

        CLI::write('Re-seeding RBAC catalog...', 'yellow');
        (new TenantProvisioningService())->seedRbac($db);
        (new CompanySettingModel($db))->bumpPermissionsVersion();

        $branchIds = array_column((new BranchModel($db))->findAll(), 'id');
        if ($branchIds === []) {
            CLI::error('No branches found in this tenant.');

            return;
        }

        $shiftIds = $this->seedShifts($db);
        $this->seedLocations($db, $branchIds);
        $this->seedHolidays($db);
        $this->seedWeeklyOffs($db);

        $employees = (new EmployeeModel($db))->select('id, branch_id')->where('status !=', 'terminated')->findAll();
        if ($employees === []) {
            CLI::write('No employees found — run employee:seed-demo first. Skipping assignment/devices/backfill.', 'yellow');
            CLI::write('Done.', 'green');

            return;
        }

        $this->assignShifts($db, $employees, $shiftIds);
        $this->seedDevices($db, $employees);
        $this->backfillAttendance($db, $employees, $shiftIds);

        CLI::write('Done.', 'green');
    }

    /** @return array<string,int> code => id */
    private function seedShifts(BaseConnection $db): array
    {
        $model  = new AttendanceShiftModel($db);
        $shifts = [
            ['name' => 'General Shift', 'code' => 'GEN', 'start_time' => '09:00:00', 'end_time' => '18:00:00', 'grace_minutes' => 10, 'late_minutes' => 15, 'half_day_minutes' => 240, 'full_day_minutes' => 480, 'is_night_shift' => 0, 'status' => 'active'],
            ['name' => 'Morning Shift', 'code' => 'MOR', 'start_time' => '06:00:00', 'end_time' => '14:00:00', 'grace_minutes' => 10, 'late_minutes' => 15, 'half_day_minutes' => 210, 'full_day_minutes' => 420, 'is_night_shift' => 0, 'status' => 'active'],
            ['name' => 'Night Shift', 'code' => 'NGT', 'start_time' => '22:00:00', 'end_time' => '06:00:00', 'grace_minutes' => 15, 'late_minutes' => 20, 'half_day_minutes' => 240, 'full_day_minutes' => 480, 'is_night_shift' => 1, 'status' => 'active'],
        ];

        $ids = [];
        foreach ($shifts as $s) {
            $existing        = $model->where('code', $s['code'])->first();
            $ids[$s['code']] = $existing ? (int) $existing['id'] : (int) $model->insert($s, true);
        }

        return $ids;
    }

    private function seedLocations(BaseConnection $db, array $branchIds): void
    {
        $model = new AttendanceLocationModel($db);
        if ($model->countAllResults() > 0) {
            return;
        }

        $coords = [
            ['name' => 'Head Office - Main Campus', 'lat' => 28.6139000, 'lng' => 77.2090000],
            ['name' => 'Branch Office', 'lat' => 19.0760000, 'lng' => 72.8777000],
        ];
        foreach ($coords as $i => $c) {
            $model->insert([
                'name' => $c['name'], 'branch_id' => $branchIds[$i] ?? $branchIds[0],
                'latitude' => $c['lat'], 'longitude' => $c['lng'], 'radius_meters' => 200, 'status' => 'active',
            ]);
        }
    }

    private function seedHolidays(BaseConnection $db): void
    {
        $model = new AttendanceHolidayModel($db);
        if ($model->countAllResults() > 0) {
            return;
        }

        foreach ([
            ["New Year's Day", '2026-01-01'], ['Republic Day', '2026-01-26'], ['Independence Day', '2026-08-15'],
            ['Gandhi Jayanti', '2026-10-02'], ['Christmas', '2026-12-25'],
        ] as [$name, $date]) {
            $model->insert(['name' => $name, 'date' => $date, 'holiday_type' => 'public', 'branch_id' => null, 'is_optional' => 0, 'status' => 'active']);
        }
    }

    private function seedWeeklyOffs(BaseConnection $db): void
    {
        $model = new AttendanceWeeklyOffModel($db);
        if ($model->countAllResults() > 0) {
            return;
        }

        $model->insert(['name' => 'Sunday Off', 'day_of_week' => 'sunday', 'week_pattern' => 'every', 'branch_id' => null, 'status' => 'active']);
        $model->insert(['name' => '2nd & 4th Saturday Off', 'day_of_week' => 'saturday', 'week_pattern' => 'alternate', 'branch_id' => null, 'status' => 'active']);
    }

    private function assignShifts(BaseConnection $db, array $employees, array $shiftIds): void
    {
        $model = new AttendanceShiftAssignmentModel($db);
        if ($model->countAllResults() > 0) {
            return;
        }

        foreach ($employees as $i => $e) {
            $shiftId = $i % 6 === 0 ? $shiftIds['NGT'] : $shiftIds['GEN'];
            $model->insert([
                'employee_id' => $e['id'], 'shift_id' => $shiftId,
                'effective_from' => date('Y-m-d', strtotime('-60 days')), 'effective_to' => null, 'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    private function seedDevices(BaseConnection $db, array $employees): void
    {
        $model = new AttendanceDeviceModel($db);
        if ($model->countAllResults() > 0) {
            return;
        }

        $statuses = ['approved', 'pending', 'blocked'];
        foreach (array_slice($employees, 0, 3) as $i => $e) {
            $status = $statuses[$i];
            $model->insert([
                'employee_id' => $e['id'], 'device_uid' => 'demo-device-' . $e['id'], 'device_name' => 'Demo Device',
                'browser' => 'Chrome', 'os' => 'Android', 'user_agent' => 'Mozilla/5.0 (demo)', 'ip_address' => '127.0.0.1',
                'first_login_at' => date('Y-m-d H:i:s', strtotime('-10 days')), 'last_login_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
                'status' => $status, 'approved_by' => $status === 'approved' ? 1 : null, 'approved_at' => $status === 'approved' ? date('Y-m-d H:i:s') : null,
            ]);
        }
    }

    private function backfillAttendance(BaseConnection $db, array $employees, array $shiftIds): void
    {
        $model = new AttendanceModel($db);
        if ($model->countAllResults() > 0) {
            return;
        }

        for ($d = 10; $d >= 1; $d--) {
            $date = date('Y-m-d', strtotime("-{$d} days"));
            $dow  = (int) date('w', strtotime($date));

            foreach ($employees as $i => $e) {
                if ($dow === 0) {
                    $model->insert(['employee_id' => $e['id'], 'attendance_date' => $date, 'status' => 'weekly_off', 'source' => 'manual']);
                    continue;
                }
                if (($i + $d) % 8 === 0) {
                    $model->insert(['employee_id' => $e['id'], 'attendance_date' => $date, 'status' => 'absent', 'source' => 'manual']);
                    continue;
                }

                $isLate = ($i + $d) % 11 === 0;
                $model->insert([
                    'employee_id' => $e['id'], 'attendance_date' => $date, 'shift_id' => $shiftIds['GEN'],
                    'first_punch_in_at' => $date . ' ' . ($isLate ? '09:25:00' : '09:02:00'),
                    'last_punch_out_at' => $date . ' 18:05:00',
                    'working_minutes' => 543, 'late_minutes' => $isLate ? 20 : 0, 'early_exit_minutes' => 0, 'overtime_minutes' => 0,
                    'status' => $isLate ? 'late' : 'present', 'source' => 'manual',
                ]);
            }
        }
    }
}
