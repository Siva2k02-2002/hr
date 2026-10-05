<?php

namespace App\Commands;

use App\Models\CompanySettingModel;
use App\Models\EmployeeModel;
use App\Models\PayrollAdvanceModel;
use App\Models\PayrollArrearsModel;
use App\Models\PayrollBonusModel;
use App\Models\PayrollEmployeeSalaryModel;
use App\Models\PayrollIncentiveModel;
use App\Models\PayrollLoanInstallmentModel;
use App\Models\PayrollLoanModel;
use App\Models\PayrollProfessionalTaxSettingModel;
use App\Models\PayrollReimbursementModel;
use App\Models\PayrollSalaryComponentModel;
use App\Models\PayrollSalaryStructureItemModel;
use App\Models\PayrollSalaryStructureModel;
use App\Services\TenantConnectionFactory;
use App\Services\TenantProvisioningService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Database\BaseConnection;

/**
 * Same shape as SeedLeaveDemoData: migrates a tenant to pick up the Payroll
 * schema, re-seeds RBAC, then seeds components/structures/assignments/loans/
 * advances/bonus/incentive/reimbursement/arrears. Runs outside an HTTP
 * request, so — like the other seed commands — it never touches
 * service('tenantContext')->db()/tenant()/session(); everything works directly off the $db
 * connection resolved from the platform database by company code, and
 * business Services (which call those request-scoped helpers internally)
 * are deliberately not used here — only Models, exactly like
 * SeedLeaveDemoData does for its own seeded rows.
 *
 * Deliberately does NOT generate a payroll run — PayrollRunService is a
 * request-scoped service (service('tenantContext')->db()/session()) and generation is better
 * exercised live through the UI as an actual HR user, which is also how the
 * approve/lock/pay workflow gets tested end-to-end. Requires employees and
 * attendance to already exist (run employee:seed-demo and
 * attendance:seed-demo first).
 */
class SeedPayrollDemoData extends BaseCommand
{
    protected $group       = 'Payroll';
    protected $name        = 'payroll:seed-demo';
    protected $description = 'Migrates + seeds demo payroll data into one tenant, by company code.';
    protected $usage       = 'payroll:seed-demo <company_code>';
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

        $this->seedPtSlabs($db);
        $componentIds = $this->seedComponents($db);
        $structureIds = $this->seedStructures($db, $componentIds);
        $this->seedAssignments($db, $employees, $structureIds);
        $this->seedLoan($db, $employees[0]);
        $this->seedAdvance($db, $employees[min(1, count($employees) - 1)]);
        $this->seedBonusAndIncentive($db, $employees);
        $this->seedReimbursement($db, $employees[0]);
        $this->seedArrears($db, $employees[0]);

        CLI::write('Done. Log in as an HR user and generate payroll from Payroll > Payroll Runs to exercise the full workflow.', 'green');
    }

    private function seedPtSlabs(BaseConnection $db): void
    {
        $model = new PayrollProfessionalTaxSettingModel($db);
        if ($model->countAllResults() > 0) {
            return;
        }

        $today = date('Y-m-d');
        $slabs = [
            ['state' => 'Tamil Nadu', 'min_gross' => 0, 'max_gross' => 21000, 'tax_amount' => 0],
            ['state' => 'Tamil Nadu', 'min_gross' => 21001, 'max_gross' => null, 'tax_amount' => 208.33],
            ['state' => 'Karnataka', 'min_gross' => 0, 'max_gross' => 15000, 'tax_amount' => 0],
            ['state' => 'Karnataka', 'min_gross' => 15001, 'max_gross' => null, 'tax_amount' => 200],
            ['state' => 'Telangana', 'min_gross' => 0, 'max_gross' => 15000, 'tax_amount' => 0],
            ['state' => 'Telangana', 'min_gross' => 15001, 'max_gross' => 20000, 'tax_amount' => 150],
            ['state' => 'Telangana', 'min_gross' => 20001, 'max_gross' => null, 'tax_amount' => 200],
            ['state' => 'Andhra Pradesh', 'min_gross' => 0, 'max_gross' => 15000, 'tax_amount' => 0],
            ['state' => 'Andhra Pradesh', 'min_gross' => 15001, 'max_gross' => 20000, 'tax_amount' => 150],
            ['state' => 'Andhra Pradesh', 'min_gross' => 20001, 'max_gross' => null, 'tax_amount' => 200],
        ];

        foreach ($slabs as $slab) {
            $model->insert($slab + ['effective_from' => $today, 'status' => 'active']);
        }
    }

    /** @return array<string,int> code => id */
    private function seedComponents(BaseConnection $db): array
    {
        $model = new PayrollSalaryComponentModel($db);

        $components = [
            ['name' => 'Basic', 'code' => 'BASIC', 'type' => 'earning', 'calculation_type' => 'percentage', 'is_taxable' => 1, 'pf_applicable' => 1, 'esi_applicable' => 1, 'display_order' => 1],
            ['name' => 'House Rent Allowance', 'code' => 'HRA', 'type' => 'earning', 'calculation_type' => 'percentage', 'percentage_of' => 'BASIC', 'is_taxable' => 1, 'esi_applicable' => 1, 'display_order' => 2],
            ['name' => 'Dearness Allowance', 'code' => 'DA', 'type' => 'earning', 'calculation_type' => 'percentage', 'percentage_of' => 'BASIC', 'is_taxable' => 1, 'pf_applicable' => 1, 'esi_applicable' => 1, 'display_order' => 3],
            ['name' => 'Conveyance Allowance', 'code' => 'CONVEYANCE', 'type' => 'earning', 'calculation_type' => 'fixed', 'is_taxable' => 0, 'esi_applicable' => 1, 'display_order' => 4],
            ['name' => 'Medical Allowance', 'code' => 'MEDICAL', 'type' => 'earning', 'calculation_type' => 'fixed', 'is_taxable' => 0, 'esi_applicable' => 1, 'display_order' => 5],
            ['name' => 'Special Allowance', 'code' => 'SPECIAL_ALLOWANCE', 'type' => 'earning', 'calculation_type' => 'formula', 'formula' => 'GROSS-BASIC-HRA-DA-CONVEYANCE-MEDICAL', 'is_taxable' => 1, 'esi_applicable' => 1, 'display_order' => 6],
            ['name' => 'Food Allowance', 'code' => 'FOOD_ALLOWANCE', 'type' => 'earning', 'calculation_type' => 'fixed', 'is_taxable' => 1, 'esi_applicable' => 1, 'display_order' => 7],
            ['name' => 'Travel Allowance', 'code' => 'TRAVEL_ALLOWANCE', 'type' => 'earning', 'calculation_type' => 'fixed', 'is_taxable' => 1, 'esi_applicable' => 1, 'display_order' => 8],
            ['name' => 'Internet Allowance', 'code' => 'INTERNET_ALLOWANCE', 'type' => 'earning', 'calculation_type' => 'fixed', 'is_taxable' => 1, 'esi_applicable' => 1, 'display_order' => 9],
            ['name' => 'Bonus', 'code' => 'BONUS', 'type' => 'earning', 'calculation_type' => 'fixed', 'is_taxable' => 1, 'display_order' => 10],
            ['name' => 'Incentive', 'code' => 'INCENTIVE', 'type' => 'earning', 'calculation_type' => 'fixed', 'is_taxable' => 1, 'display_order' => 11],
            ['name' => 'Overtime', 'code' => 'OVERTIME', 'type' => 'earning', 'calculation_type' => 'fixed', 'is_taxable' => 1, 'display_order' => 12],
            ['name' => 'Arrears', 'code' => 'ARREARS', 'type' => 'earning', 'calculation_type' => 'fixed', 'is_taxable' => 1, 'display_order' => 13],
            ['name' => 'Reimbursement', 'code' => 'REIMBURSEMENT', 'type' => 'earning', 'calculation_type' => 'fixed', 'is_taxable' => 0, 'display_order' => 14],
            ['name' => 'Provident Fund', 'code' => 'PF', 'type' => 'deduction', 'calculation_type' => 'fixed', 'is_taxable' => 0, 'display_order' => 15],
            ['name' => 'ESI', 'code' => 'ESI', 'type' => 'deduction', 'calculation_type' => 'fixed', 'is_taxable' => 0, 'display_order' => 16],
            ['name' => 'Professional Tax', 'code' => 'PT', 'type' => 'deduction', 'calculation_type' => 'fixed', 'is_taxable' => 0, 'display_order' => 17],
            ['name' => 'TDS', 'code' => 'TDS', 'type' => 'deduction', 'calculation_type' => 'fixed', 'is_taxable' => 0, 'display_order' => 18],
            ['name' => 'Loan EMI', 'code' => 'LOAN_EMI', 'type' => 'deduction', 'calculation_type' => 'fixed', 'is_taxable' => 0, 'display_order' => 19],
            ['name' => 'Salary Advance', 'code' => 'SALARY_ADVANCE', 'type' => 'deduction', 'calculation_type' => 'fixed', 'is_taxable' => 0, 'display_order' => 20],
            ['name' => 'Loss of Pay', 'code' => 'LOP', 'type' => 'deduction', 'calculation_type' => 'fixed', 'is_taxable' => 0, 'display_order' => 21],
            ['name' => 'Other Deduction', 'code' => 'OTHER_DEDUCTION', 'type' => 'deduction', 'calculation_type' => 'fixed', 'is_taxable' => 0, 'display_order' => 22],
        ];

        $ids = [];
        foreach ($components as $c) {
            $existing        = $model->where('code', $c['code'])->first();
            $ids[$c['code']] = $existing ? (int) $existing['id'] : (int) $model->insert($c + ['status' => 'active'], true);
        }

        return $ids;
    }

    /** @return array<string,int> structure name => id */
    private function seedStructures(BaseConnection $db, array $componentIds): array
    {
        $structures = new PayrollSalaryStructureModel($db);
        $items      = new PayrollSalaryStructureItemModel($db);

        $earningCodes = ['BASIC', 'HRA', 'DA', 'CONVEYANCE', 'MEDICAL', 'SPECIAL_ALLOWANCE'];
        $names        = ['Software Engineer', 'HR Executive', 'Manager', 'Sales Executive'];

        $ids = [];
        foreach ($names as $name) {
            $existing = $structures->where('name', $name)->first();
            $id       = $existing ? (int) $existing['id'] : (int) $structures->insert([
                'name' => $name, 'description' => $name . ' salary structure', 'effective_from' => date('Y-m-d'), 'status' => 'active',
            ], true);
            $ids[$name] = $id;

            if ($existing) {
                continue;
            }

            $values = ['BASIC' => 50, 'HRA' => 40, 'DA' => 10, 'CONVEYANCE' => 1600, 'MEDICAL' => 1250, 'SPECIAL_ALLOWANCE' => 0];
            foreach ($earningCodes as $order => $code) {
                $calcType = in_array($code, ['CONVEYANCE', 'MEDICAL'], true) ? 'fixed' : ($code === 'SPECIAL_ALLOWANCE' ? 'formula' : 'percentage');
                $items->insert([
                    'salary_structure_id' => $id, 'salary_component_id' => $componentIds[$code],
                    'calculation_type' => $calcType, 'value' => $calcType === 'fixed' ? $values[$code] : ($calcType === 'percentage' ? $values[$code] : 0),
                    'formula' => $calcType === 'formula' ? 'GROSS-BASIC-HRA-DA-CONVEYANCE-MEDICAL' : null,
                    'is_editable' => 1, 'display_order' => $order,
                ]);
            }
        }

        return $ids;
    }

    private function seedAssignments(BaseConnection $db, array $employees, array $structureIds): void
    {
        $model = new PayrollEmployeeSalaryModel($db);
        if ($model->countAllResults() > 0) {
            return;
        }

        $structureList = array_values($structureIds);
        $grossOptions  = [18000, 28000, 45000, 75000]; // spans below/above PF+ESI ceilings for realistic test data

        foreach ($employees as $i => $employee) {
            $structureId = $structureList[$i % count($structureList)];
            $gross       = $grossOptions[$i % count($grossOptions)];

            $model->insert([
                'employee_id' => $employee['id'], 'salary_structure_id' => $structureId,
                'effective_from' => $employee['date_of_joining'] > date('Y-m-d') ? date('Y-m-d') : max($employee['date_of_joining'], date('Y-m-01', strtotime('-6 months'))),
                'gross_salary' => $gross, 'ctc' => $gross * 12 * 1.15, 'status' => 'active',
            ]);
        }
    }

    private function seedLoan(BaseConnection $db, array $employee): void
    {
        $model = new PayrollLoanModel($db);
        if ($model->where('employee_id', $employee['id'])->countAllResults() > 0) {
            return;
        }

        $id = $model->insert([
            'employee_id' => $employee['id'], 'loan_number' => 'LN-' . $employee['employee_code'], 'loan_type' => 'Personal Loan',
            'principal_amount' => 60000, 'interest_rate' => 10, 'tenure_months' => 12, 'emi_amount' => 5275,
            'start_month' => (int) date('n'), 'start_year' => (int) date('Y'), 'outstanding_balance' => 60000, 'status' => 'active',
        ], true);

        $installments = new PayrollLoanInstallmentModel($db);
        $balance      = 60000.0;
        $month        = (int) date('n');
        $year         = (int) date('Y');
        for ($i = 1; $i <= 12; $i++) {
            $interest  = round($balance * (10 / 12 / 100), 2);
            $principal = min($balance, round(5275 - $interest, 2));
            $balance   = round($balance - $principal, 2);
            $installments->insert([
                'loan_id' => $id, 'installment_no' => $i, 'due_year' => $year, 'due_month' => $month,
                'emi_amount' => round($principal + $interest, 2), 'principal_component' => $principal, 'interest_component' => $interest, 'status' => 'pending',
            ]);
            $month++;
            if ($month > 12) {
                $month = 1;
                $year++;
            }
        }
    }

    private function seedAdvance(BaseConnection $db, array $employee): void
    {
        $model = new PayrollAdvanceModel($db);
        if ($model->where('employee_id', $employee['id'])->countAllResults() > 0) {
            return;
        }

        $model->insert([
            'employee_id' => $employee['id'], 'amount' => 10000, 'advance_date' => date('Y-m-d'),
            'recovery_type' => 'installments', 'installments_count' => 4, 'installment_amount' => 2500,
            'recovered_amount' => 0, 'remaining_balance' => 10000, 'status' => 'active', 'reason' => 'Personal emergency',
        ]);
    }

    private function seedBonusAndIncentive(BaseConnection $db, array $employees): void
    {
        $bonusModel     = new PayrollBonusModel($db);
        $incentiveModel = new PayrollIncentiveModel($db);
        if ($bonusModel->countAllResults() > 0) {
            return;
        }

        $batchId = uniqid('demo_bonus_', true);
        foreach (array_slice($employees, 0, min(3, count($employees))) as $employee) {
            $bonusModel->insert([
                'bonus_batch_id' => $batchId, 'employee_id' => $employee['id'], 'bonus_type' => 'festival',
                'amount' => 5000, 'remarks' => 'Festival bonus (demo)', 'status' => 'pending',
            ]);
        }

        $incentiveModel->insert([
            'employee_id' => $employees[0]['id'], 'incentive_type' => 'Performance Incentive', 'amount' => 3000,
            'remarks' => 'Q1 performance incentive (demo)', 'status' => 'pending',
        ]);
    }

    private function seedReimbursement(BaseConnection $db, array $employee): void
    {
        $model = new PayrollReimbursementModel($db);
        if ($model->where('employee_id', $employee['id'])->countAllResults() > 0) {
            return;
        }

        $model->insert([
            'employee_id' => $employee['id'], 'expense_type' => 'Travel', 'amount' => 2200, 'expense_date' => date('Y-m-d'),
            'status' => 'approved', 'approved_at' => date('Y-m-d H:i:s'), 'remarks' => 'Client visit travel (demo)',
        ]);
    }

    private function seedArrears(BaseConnection $db, array $employee): void
    {
        $model = new PayrollArrearsModel($db);
        if ($model->where('employee_id', $employee['id'])->countAllResults() > 0) {
            return;
        }

        $model->insert([
            'employee_id' => $employee['id'], 'from_month' => (int) date('n', strtotime('-2 months')), 'from_year' => (int) date('Y', strtotime('-2 months')),
            'to_month' => (int) date('n', strtotime('-1 month')), 'to_year' => (int) date('Y', strtotime('-1 month')),
            'amount' => 4000, 'reason' => 'Salary revision arrears (demo)', 'status' => 'pending',
        ]);
    }
}
