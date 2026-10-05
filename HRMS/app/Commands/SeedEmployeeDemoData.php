<?php

namespace App\Commands;

use App\Models\BranchModel;
use App\Models\CompanySettingModel;
use App\Models\DepartmentModel;
use App\Models\DesignationModel;
use App\Models\EmployeeModel;
use App\Models\EmployeeStatusHistoryModel;
use App\Services\TenantConnectionFactory;
use App\Services\TenantProvisioningService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Database\BaseConnection;

/**
 * Dev/test-only helper: brings an already-provisioned tenant's schema up to
 * date with the Employee Master migrations + RBAC catalog, then seeds
 * representative employee data across branches/departments/designations/
 * statuses/managers (Phase 4 spec's "TEST DATA" section).
 *
 * Runs outside an HTTP request, so it never touches service('tenantContext')->db()/tenant()/
 * session() — those all depend on TenantResolver having run for a real
 * request. Everything here works directly off the $db connection resolved
 * from the platform database by company code.
 */
class SeedEmployeeDemoData extends BaseCommand
{
    protected $group       = 'Employees';
    protected $name        = 'employee:seed-demo';
    protected $description = 'Migrates + seeds demo employee data into one tenant, by company code.';
    protected $usage       = 'employee:seed-demo <company_code> [--reset-password]';
    protected $arguments   = ['company_code' => 'The companies.code value, e.g. abc'];
    protected $options     = ['--reset-password' => "Reset that tenant's Company Admin password to a known dev value and print it"];

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

        if (CLI::getOption('reset-password')) {
            $this->resetAdminPassword($db);
        }

        [$branchId, $mumbaiId, $blrId, $engDeptId, $salesDeptId, $hrDeptId, $finDeptId, $desigIds] = $this->seedOrgStructure($db);
        $this->seedEmployees($db, $branchId, $mumbaiId, $blrId, $engDeptId, $salesDeptId, $hrDeptId, $finDeptId, $desigIds);

        CLI::write('Done.', 'green');
    }

    private function resetAdminPassword(BaseConnection $db): void
    {
        $admin = $db->table('users u')
            ->select('u.id, u.email')
            ->join('user_roles ur', 'ur.user_id = u.id')
            ->join('roles r', 'r.id = ur.role_id')
            ->where('r.slug', 'company-admin')
            ->get()
            ->getRowArray();

        if (! $admin) {
            CLI::error('No company-admin user found to reset.');

            return;
        }

        $newPassword = 'Passw0rd!123';
        $db->table('users')->where('id', $admin['id'])->update([
            'password_hash'         => password_hash($newPassword, PASSWORD_DEFAULT),
            'must_change_password'  => 0,
            'locked_until'          => null,
            'failed_login_attempts' => 0,
        ]);

        CLI::write("Reset login for {$admin['email']} — password: {$newPassword}", 'green');
    }

    /** @return array{0:int,1:int,2:int,3:int,4:int,5:int,6:int,7:array<string,int>} */
    private function seedOrgStructure(BaseConnection $db): array
    {
        $branches = new BranchModel($db);
        $branchId = $this->firstOrCreate($branches, 'Head Office', ['code' => 'HO', 'status' => 'active']);
        $mumbaiId = $this->firstOrCreate($branches, 'Mumbai Branch', ['code' => 'MUM', 'status' => 'active']);
        $blrId    = $this->firstOrCreate($branches, 'Bangalore Branch', ['code' => 'BLR', 'status' => 'active']);

        $departments = new DepartmentModel($db);
        $engDeptId   = $this->firstOrCreate($departments, 'Engineering', ['code' => 'ENG', 'branch_id' => $branchId, 'status' => 'active']);
        $salesDeptId = $this->firstOrCreate($departments, 'Sales', ['code' => 'SALES', 'branch_id' => $mumbaiId, 'status' => 'active']);
        $hrDeptId    = $this->firstOrCreate($departments, 'Human Resources', ['code' => 'HR', 'branch_id' => $branchId, 'status' => 'active']);
        $finDeptId   = $this->firstOrCreate($departments, 'Finance', ['code' => 'FIN', 'branch_id' => $blrId, 'status' => 'active']);

        $designations = new DesignationModel($db);
        $desigIds     = [
            'eng_manager' => $this->firstOrCreate($designations, 'Engineering Manager', ['department_id' => $engDeptId, 'level' => 3, 'status' => 'active']),
            'sr_swe'      => $this->firstOrCreate($designations, 'Senior Software Engineer', ['department_id' => $engDeptId, 'level' => 2, 'status' => 'active']),
            'swe'         => $this->firstOrCreate($designations, 'Software Engineer', ['department_id' => $engDeptId, 'level' => 1, 'status' => 'active']),
            'sales_exec'  => $this->firstOrCreate($designations, 'Sales Executive', ['department_id' => $salesDeptId, 'level' => 1, 'status' => 'active']),
            'hr_exec'     => $this->firstOrCreate($designations, 'HR Executive', ['department_id' => $hrDeptId, 'level' => 1, 'status' => 'active']),
            'fin_analyst' => $this->firstOrCreate($designations, 'Finance Analyst', ['department_id' => $finDeptId, 'level' => 1, 'status' => 'active']),
        ];

        return [$branchId, $mumbaiId, $blrId, $engDeptId, $salesDeptId, $hrDeptId, $finDeptId, $desigIds];
    }

    private function firstOrCreate($model, string $name, array $extra): int
    {
        $existing = $model->where('name', $name)->first();
        if ($existing) {
            return (int) $existing['id'];
        }

        return (int) $model->insert(['name' => $name, ...$extra], true);
    }

    private function seedEmployees(BaseConnection $db, int $ho, int $mum, int $blr, int $eng, int $sales, int $hr, int $fin, array $d): void
    {
        $employees = new EmployeeModel($db);
        $history   = new EmployeeStatusHistoryModel($db);

        if ($employees->countAllResults() > 0) {
            CLI::write('Employees already exist — skipping demo data seed.', 'yellow');

            return;
        }

        $rows = [
            ['Rajesh', 'Kumar',  'male',   $ho,   $eng,   $d['eng_manager'], 'active',        null, '2019-03-01'],
            ['Priya',  'Sharma', 'female', $ho,   $eng,   $d['sr_swe'],      'active',        0,    '2020-06-15'],
            ['Amit',   'Verma',  'male',   $ho,   $eng,   $d['swe'],         'probation',     1,    '2026-07-01'],
            ['Sunita', 'Rao',    'female', $ho,   $eng,   $d['swe'],         'active',        1,    '2021-11-10'],
            ['Sanjay', 'Patel',  'male',   $ho,   $eng,   $d['swe'],         'relieved',      1,    '2018-01-20'],
            ['Vikram', 'Singh',  'male',   $mum,  $sales, $d['sales_exec'],  'active',        null, '2019-09-05'],
            ['Neha',   'Gupta',  'female', $mum,  $sales, $d['sales_exec'],  'notice_period', 5,    '2022-02-14'],
            ['Kavita', 'Joshi',  'female', $mum,  $sales, $d['sales_exec'],  'terminated',    5,    '2020-04-01'],
            ['Karan',  'Mehta',  'male',   $ho,   $hr,    $d['hr_exec'],     'active',        null, '2020-01-15'],
            ['Anjali', 'Nair',   'female', $ho,   $hr,    $d['hr_exec'],     'suspended',     8,    '2021-07-20'],
            ['Rohit',  'Desai',  'male',   $blr,  $fin,   $d['fin_analyst'], 'active',        null, '2019-05-12'],
            ['Meera',  'Iyer',   'female', $blr,  $fin,   $d['fin_analyst'], 'resigned',      10,   '2020-10-01'],
        ];

        $idByRow = [];
        $mobile  = 9800000000;

        foreach ($rows as $i => [$first, $last, $gender, $branchId, $deptId, $desigId, $status, $managerRow, $joined]) {
            $code = $this->nextEmployeeCode($db);

            $id = $employees->insert([
                'employee_code'        => $code,
                'first_name'           => $first,
                'last_name'            => $last,
                'gender'               => $gender,
                'date_of_birth'        => sprintf('19%d-0%d-1%d', 80 + ($i % 15), 1 + ($i % 9), $i % 8),
                'mobile'                => (string) ($mobile + $i),
                'company_email'         => strtolower($first . '.' . $last) . '@abc.test',
                'branch_id'             => $branchId,
                'department_id'         => $deptId,
                'designation_id'        => $desigId,
                'reporting_manager_id'  => $managerRow !== null ? ($idByRow[$managerRow] ?? null) : null,
                'employment_type'       => 'full_time',
                'date_of_joining'       => $joined,
                'status'                => $status,
            ], true);

            $idByRow[$i] = $id;

            $history->insert([
                'employee_id' => $id, 'event_type' => 'joined', 'to_value' => $status,
                'created_at'  => date('Y-m-d H:i:s'),
            ]);
        }

        CLI::write('Seeded ' . count($rows) . ' demo employees.', 'green');
    }

    private function nextEmployeeCode(BaseConnection $db): string
    {
        $db->transStart();
        $row = $db->query('SELECT id, employee_code_prefix, employee_code_next_seq FROM company_settings ORDER BY id ASC LIMIT 1 FOR UPDATE')->getRowArray();
        $seq  = (int) $row['employee_code_next_seq'];
        $code = $row['employee_code_prefix'] . str_pad((string) $seq, 6, '0', STR_PAD_LEFT);
        $db->table('company_settings')->where('id', $row['id'])->update(['employee_code_next_seq' => $seq + 1]);
        $db->transComplete();

        return $code;
    }
}
