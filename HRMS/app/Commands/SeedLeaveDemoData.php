<?php

namespace App\Commands;

use App\Models\AttendanceModel;
use App\Models\CompanySettingModel;
use App\Models\EmployeeLeaveBalanceModel;
use App\Models\EmployeeModel;
use App\Models\LeaveApplicationDayModel;
use App\Models\LeaveApplicationModel;
use App\Models\LeaveApprovalHistoryModel;
use App\Models\LeavePolicyModel;
use App\Models\LeavePolicyRuleModel;
use App\Models\LeaveSettingModel;
use App\Models\LeaveTypeModel;
use App\Services\TenantConnectionFactory;
use App\Services\TenantProvisioningService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Database\BaseConnection;

/**
 * Same shape as SeedAttendanceDemoData: migrates a tenant to pick up the
 * Leave schema, re-seeds RBAC, then seeds types/policy/rules/balances/demo
 * applications. Runs outside an HTTP request, so — like the Employee and
 * Attendance seed commands — it never touches service('tenantContext')->db()/tenant()/session();
 * everything works directly off the $db connection resolved from the
 * platform database by company code. Requires employees and holidays/
 * weekly-offs to already exist (run employee:seed-demo and
 * attendance:seed-demo first).
 *
 * Demo applications don't include a hand-crafted sandwich-leave scenario:
 * doing that correctly requires picking dates that actually straddle a
 * seeded holiday+weekend combination relative to whenever this command is
 * run, which would be fragile against a fixed holiday calendar. Verify
 * sandwich-leave live instead, by applying across one of the seeded
 * holidays via the Apply Leave screen.
 */
class SeedLeaveDemoData extends BaseCommand
{
    protected $group       = 'Leave';
    protected $name        = 'leave:seed-demo';
    protected $description = 'Migrates + seeds demo leave data into one tenant, by company code.';
    protected $usage       = 'leave:seed-demo <company_code>';
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

        $employees = (new EmployeeModel($db))->where('status !=', 'terminated')->findAll();
        if ($employees === []) {
            CLI::error('No employees found — run employee:seed-demo first.');

            return;
        }

        $settings   = $this->seedSettings($db);
        $typeIds    = $this->seedLeaveTypes($db);
        $policyId   = $this->seedDefaultPolicy($db);
        $this->seedPolicyRules($db, $policyId, $typeIds);

        $financialYear = $this->financialYearFor(date('Y-m-d'), (int) $settings['financial_year_start_month']);
        $this->seedBalances($db, $employees, $typeIds, $policyId, $financialYear);
        $this->seedApplications($db, $employees, $typeIds, $policyId, $financialYear);

        CLI::write('Done.', 'green');
    }

    private function seedSettings(BaseConnection $db): array
    {
        $model = new LeaveSettingModel($db);
        $row   = $model->orderBy('id', 'asc')->first();
        if ($row) {
            return $row;
        }

        $id = $model->insert([
            'financial_year_start_month' => 1, 'leave_year_start_month' => 1, 'half_day_enabled' => 1,
            'sandwich_leave_enabled' => 1, 'holiday_between_leave_policy' => 'count', 'weekly_off_between_leave_policy' => 'count',
            'carry_forward_enabled' => 1, 'carry_forward_limit' => 6, 'leave_encashment_enabled' => 1,
            'min_notice_days' => 0, 'max_future_apply_days' => 180, 'allow_negative_balance' => 0,
            'self_approval_allowed_for_admin' => 1, 'half_day_hours' => 4.0,
        ], true);

        return $model->find($id);
    }

    /** @return array<string,int> code => id */
    private function seedLeaveTypes(BaseConnection $db): array
    {
        $model = new LeaveTypeModel($db);
        $types = [
            ['name' => 'Casual Leave', 'code' => 'CL', 'color' => '#3B82F6', 'is_paid' => 1, 'annual_allocation' => 12, 'half_day_allowed' => 1, 'carry_forward_allowed' => 1, 'encashment_allowed' => 0, 'attendance_status_map' => 'leave'],
            ['name' => 'Sick Leave', 'code' => 'SL', 'color' => '#F59E0B', 'is_paid' => 1, 'annual_allocation' => 10, 'half_day_allowed' => 1, 'medical_certificate_required' => 1, 'carry_forward_allowed' => 0, 'encashment_allowed' => 0, 'attendance_status_map' => 'leave'],
            ['name' => 'Earned Leave', 'code' => 'EL', 'color' => '#10B981', 'is_paid' => 1, 'annual_allocation' => 18, 'half_day_allowed' => 1, 'carry_forward_allowed' => 1, 'encashment_allowed' => 1, 'attendance_status_map' => 'leave'],
            ['name' => 'Loss of Pay', 'code' => 'LOP', 'color' => '#EF4444', 'is_paid' => 0, 'annual_allocation' => 0, 'half_day_allowed' => 0, 'carry_forward_allowed' => 0, 'encashment_allowed' => 0, 'attendance_status_map' => 'lop'],
            ['name' => 'Compensatory Off', 'code' => 'COMP', 'color' => '#8B5CF6', 'is_paid' => 1, 'annual_allocation' => 0, 'half_day_allowed' => 1, 'carry_forward_allowed' => 0, 'encashment_allowed' => 0, 'attendance_status_map' => 'leave'],
            ['name' => 'Work From Home', 'code' => 'WFH', 'color' => '#06B6D4', 'is_paid' => 1, 'annual_allocation' => 0, 'half_day_allowed' => 1, 'carry_forward_allowed' => 0, 'encashment_allowed' => 0, 'attendance_status_map' => 'work_from_home'],
            ['name' => 'On Duty', 'code' => 'ONDUTY', 'color' => '#6366F1', 'is_paid' => 1, 'annual_allocation' => 0, 'half_day_allowed' => 0, 'carry_forward_allowed' => 0, 'encashment_allowed' => 0, 'attendance_status_map' => 'on_duty'],
        ];

        $ids = [];
        foreach ($types as $t) {
            $existing        = $model->where('code', $t['code'])->first();
            $ids[$t['code']] = $existing ? (int) $existing['id'] : (int) $model->insert($t, true);
        }

        return $ids;
    }

    private function seedDefaultPolicy(BaseConnection $db): int
    {
        $model    = new LeavePolicyModel($db);
        $existing = $model->where('is_default', 1)->first();
        if ($existing) {
            return (int) $existing['id'];
        }

        return (int) $model->insert([
            'name' => 'Standard Company Policy', 'description' => 'Company-wide default leave policy.',
            'is_default' => 1, 'priority' => 0, 'status' => 'active',
        ], true);
    }

    private function seedPolicyRules(BaseConnection $db, int $policyId, array $typeIds): void
    {
        $model = new LeavePolicyRuleModel($db);
        if ($model->where('leave_policy_id', $policyId)->countAllResults() > 0) {
            return;
        }

        $rules = [
            'CL'     => ['annual_allocation' => 12, 'carry_forward_allowed' => 1, 'carry_forward_limit' => 6, 'max_consecutive_days' => 3],
            'SL'     => ['annual_allocation' => 10, 'carry_forward_allowed' => 0, 'max_consecutive_days' => null],
            'EL'     => ['annual_allocation' => 18, 'carry_forward_allowed' => 1, 'carry_forward_unlimited' => 1, 'encashment_allowed' => 1],
            'LOP'    => ['annual_allocation' => 0],
            'COMP'   => ['annual_allocation' => 0],
            'WFH'    => ['annual_allocation' => 0, 'min_days_per_application' => 0.5],
            'ONDUTY' => ['annual_allocation' => 0],
        ];

        foreach ($rules as $code => $r) {
            if (! isset($typeIds[$code])) {
                continue;
            }
            $model->insert($r + [
                'leave_policy_id' => $policyId, 'leave_type_id' => $typeIds[$code],
                'min_days_per_application' => 0.5, 'sandwich_rule_applicable' => 1, 'status' => 'active',
            ]);
        }
    }

    private function seedBalances(BaseConnection $db, array $employees, array $typeIds, int $policyId, int $financialYear): void
    {
        $model = new EmployeeLeaveBalanceModel($db);
        if ($model->countAllResults() > 0) {
            return;
        }

        $allocations = ['CL' => 12, 'SL' => 10, 'EL' => 18, 'LOP' => 0, 'COMP' => 0, 'WFH' => 0, 'ONDUTY' => 0];
        foreach ($employees as $employee) {
            foreach ($typeIds as $code => $typeId) {
                $earned = $allocations[$code] ?? 0;
                $model->insert([
                    'employee_id' => $employee['id'], 'leave_type_id' => $typeId, 'leave_policy_id' => $policyId,
                    'financial_year' => $financialYear, 'opening_balance' => 0, 'earned' => $earned, 'availed' => 0,
                    'adjusted' => 0, 'carry_forward_in' => 0, 'carry_forward_out' => 0, 'encashed' => 0,
                    'closing_balance' => $earned, 'last_transaction_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }
    }

    private function seedApplications(BaseConnection $db, array $employees, array $typeIds, int $policyId, int $financialYear): void
    {
        $model = new LeaveApplicationModel($db);
        if ($model->countAllResults() > 0) {
            return;
        }

        $scenarios = ['pending_level1', 'pending_level2', 'approved_half_day', 'approved_full_day', 'rejected', 'cancelled'];
        foreach ($scenarios as $i => $scenario) {
            if (! isset($employees[$i])) {
                break;
            }
            $this->seedOneApplication($db, $employees[$i], $typeIds['CL'], $policyId, $financialYear, $scenario, $i);
        }
    }

    private function seedOneApplication(BaseConnection $db, array $employee, int $leaveTypeId, int $policyId, int $financialYear, string $scenario, int $offset): void
    {
        $applications = new LeaveApplicationModel($db);
        $days         = new LeaveApplicationDayModel($db);
        $history      = new LeaveApprovalHistoryModel($db);
        $now          = date('Y-m-d H:i:s');

        $isHalfDay = $scenario === 'approved_half_day';
        $fromDate  = date('Y-m-d', strtotime('+' . (3 + $offset) . ' days'));
        $toDate    = $isHalfDay ? $fromDate : date('Y-m-d', strtotime($fromDate . ' +1 day'));
        $totalDays = $isHalfDay ? 0.5 : 2.0;

        $manager  = ! empty($employee['reporting_manager_id']) ? (new EmployeeModel($db))->find($employee['reporting_manager_id']) : null;
        $hasLevel1 = ! empty($manager['user_id'] ?? null);

        $data = [
            'employee_id' => $employee['id'], 'leave_type_id' => $leaveTypeId, 'leave_policy_id' => $policyId,
            'from_date' => $fromDate, 'to_date' => $toDate, 'is_half_day' => $isHalfDay ? 1 : 0,
            'half_day_session' => $isHalfDay ? 'first_half' : null, 'total_days' => $totalDays,
            'reason' => 'Demo seed data — ' . str_replace('_', ' ', $scenario),
            'status' => 'pending', 'current_level' => $hasLevel1 ? 'level1' : 'level2',
            'level1_approver_id' => $hasLevel1 ? $manager['user_id'] : null,
            'level1_status' => $hasLevel1 ? 'pending' : 'skipped',
            'submitted_at' => $now, 'created_by' => null,
        ];

        switch ($scenario) {
            case 'pending_level2':
                $data['current_level']  = 'level2';
                $data['level1_status']  = $hasLevel1 ? 'approved' : 'skipped';
                $data['level1_acted_at'] = $now;
                break;
            case 'approved_half_day':
            case 'approved_full_day':
                $data['status']         = 'approved';
                $data['current_level']  = 'completed';
                $data['level1_status']  = $hasLevel1 ? 'approved' : 'skipped';
                $data['level1_acted_at'] = $now;
                $data['level2_status']  = 'approved';
                $data['level2_acted_at'] = $now;
                $data['balance_deducted']   = 1;
                $data['attendance_applied'] = 1;
                break;
            case 'rejected':
                $data['status']        = 'rejected';
                $data['level1_status'] = $hasLevel1 ? 'approved' : 'skipped';
                $data['level2_status'] = 'rejected';
                $data['level2_acted_at'] = $now;
                break;
            case 'cancelled':
                $data['status']       = 'cancelled';
                $data['cancelled_at'] = $now;
                $data['cancellation_reason'] = 'Demo seed data — cancelled example';
                break;
        }

        $id = $applications->insert($data, true);

        $dayType = $isHalfDay ? 'half_first' : 'full';
        $cursor  = strtotime($fromDate);
        $end     = strtotime($toDate);
        while ($cursor <= $end) {
            $days->insert([
                'leave_application_id' => $id, 'leave_date' => date('Y-m-d', $cursor), 'day_type' => $dayType,
                'day_category' => 'working', 'is_sandwiched' => 0, 'counts_as_leave' => 1,
                'day_value' => $isHalfDay ? 0.5 : 1.0,
            ]);
            $cursor = strtotime('+1 day', $cursor);
        }

        $history->insert(['leave_application_id' => $id, 'action' => 'submitted', 'actor_id' => null, 'snapshot_status' => 'pending', 'created_at' => $now]);

        if (in_array($scenario, ['approved_half_day', 'approved_full_day'], true)) {
            $balances = new EmployeeLeaveBalanceModel($db);
            $balance  = $balances->where('employee_id', $employee['id'])->where('leave_type_id', $leaveTypeId)->where('financial_year', $financialYear)->first();
            if ($balance) {
                $balances->update($balance['id'], [
                    'availed' => (float) $balance['availed'] + $totalDays,
                    'closing_balance' => (float) $balance['closing_balance'] - $totalDays,
                    'last_transaction_at' => $now,
                ]);
            }

            $attendanceModel = new AttendanceModel($db);
            $status          = $isHalfDay ? 'half_day_leave' : 'leave';
            $cursor          = strtotime($fromDate);
            while ($cursor <= $end) {
                $date     = date('Y-m-d', $cursor);
                $existing = $attendanceModel->forEmployeeAndDate((int) $employee['id'], $date);
                $rowData  = ['employee_id' => $employee['id'], 'attendance_date' => $date, 'status' => $status, 'source' => 'leave'];
                $existing ? $attendanceModel->update($existing['id'], $rowData) : $attendanceModel->insert($rowData, true);
                $cursor = strtotime('+1 day', $cursor);
            }

            $history->insert(['leave_application_id' => $id, 'action' => 'level2_approved', 'level' => 'level2', 'actor_id' => null, 'snapshot_status' => 'approved', 'created_at' => $now]);
        } elseif ($scenario === 'rejected') {
            $history->insert(['leave_application_id' => $id, 'action' => 'level2_rejected', 'level' => 'level2', 'actor_id' => null, 'snapshot_status' => 'rejected', 'created_at' => $now]);
        } elseif ($scenario === 'cancelled') {
            $history->insert(['leave_application_id' => $id, 'action' => 'cancelled', 'actor_id' => null, 'snapshot_status' => 'cancelled', 'created_at' => $now]);
        }
    }

    /** Mirrors leave_helper.php's leave_financial_year() without needing service('tenantContext')->db()/session() — this command runs outside an HTTP request. */
    private function financialYearFor(string $date, int $startMonth): int
    {
        $year  = (int) date('Y', strtotime($date));
        $month = (int) date('n', strtotime($date));

        return $month >= $startMonth ? $year : $year - 1;
    }
}
