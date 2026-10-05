<?php

namespace App\Services;

use App\Models\EmployeeModel;
use App\Models\PayrollEmployeeSalaryModel;
use App\Models\PayrollMonthModel;
use App\Models\PayrollRunItemModel;
use App\Models\PayrollRunModel;
use App\Models\PayrollSettingModel;
use RuntimeException;

/**
 * The generation orchestrator: Employees -> Attendance Summary -> Leave
 * Summary (folded into the attendance read) -> Salary Structure -> Earnings
 * -> Deductions -> Net Salary -> Draft Payroll, per the spec's flow diagram.
 * Only one non-cancelled run per payroll_month is ever allowed — that check
 * lives here, not in the schema, so a cancelled+regenerated run stays
 * auditable. Nothing here mutates loan/advance/bonus/incentive/arrears/
 * reimbursement rows — those commits happen only in PayrollApprovalService,
 * so a draft run is always safe to inspect, edit, or cancel and regenerate.
 */
class PayrollRunService
{
    public function __construct(
        private AuditService $audit = new AuditService(),
        private PayrollAttendanceIntegrationService $attendance = new PayrollAttendanceIntegrationService(),
        private PayrollEarningsService $earningsService = new PayrollEarningsService(),
        private PayrollDeductionsService $deductionsService = new PayrollDeductionsService(),
        private NotificationService $notifications = new NotificationService()
    ) {
    }

    /** @return array{run: array, processed: int, skipped: array<int, array{employee: string, reason: string}>} */
    public function generate(int $month, int $year): array
    {
        $db       = service('tenantContext')->db();
        $settings = (new PayrollSettingModel($db))->current();

        [$startDate, $endDate] = $this->periodFor($month, $year, $settings);

        $monthModel   = new PayrollMonthModel($db);
        $payrollMonth = $monthModel->resolveOrCreate($month, $year, $startDate, $endDate);

        $runModel = new PayrollRunModel($db);

        // Check-then-insert on its own is a race: two near-simultaneous "Generate Payroll"
        // clicks (a double-click, two admins, a retried request) can both pass
        // activeForMonth() before either commits its insert, producing two active runs for
        // the same month (audit finding PAY-02). Locking the payroll_months row serializes
        // that check+insert the same way AttendancePunchService locks the employee's latest
        // punch — the second request blocks until the first commits, then re-reads current state.
        $db->transStart();
        $monthModel->lockForUpdate((int) $payrollMonth['id']);

        if ($runModel->activeForMonth((int) $payrollMonth['id'])) {
            $db->transComplete();

            throw new RuntimeException('Payroll has already been generated for this month. Cancel the existing run first to regenerate.');
        }

        $runId = $runModel->insert([
            'payroll_month_id' => $payrollMonth['id'],
            'run_type'         => 'regular',
            'status'           => 'draft',
            'generated_by'     => session('tenant_user_id'),
            'generated_at'     => date('Y-m-d H:i:s'),
        ], true);

        $db->transComplete();
        if ($db->transStatus() === false) {
            throw new RuntimeException('Could not start payroll generation — please try again.');
        }

        $employees = $this->eligibleEmployees($db, $startDate, $endDate);
        $itemModel = new PayrollRunItemModel($db);
        $skipped   = [];
        $totals    = ['gross' => 0.0, 'net' => 0.0, 'pf' => 0.0, 'esi' => 0.0, 'pt' => 0.0, 'tds' => 0.0];
        $processed = 0;

        foreach ($employees as $employee) {
            $name = trim($employee['first_name'] . ' ' . $employee['last_name']);

            $assignment = (new PayrollEmployeeSalaryModel($db))->activeFor((int) $employee['id'], $endDate);
            if (! $assignment) {
                $skipped[] = ['employee' => $name, 'reason' => 'No salary structure assigned.'];
                continue;
            }

            $attendanceSummary = $this->attendance->summarize((int) $employee['id'], $startDate, $endDate, $settings['working_days_basis'], (int) $settings['fixed_working_days']);
            if ($attendanceSummary['working_days'] <= 0 && $attendanceSummary['present_days'] <= 0 && $attendanceSummary['lop_days'] <= 0) {
                $skipped[] = ['employee' => $name, 'reason' => 'No attendance data for this period.'];
                continue;
            }

            $payrollMonthId = (int) $payrollMonth['id'];
            $earnings       = $this->earningsService->compute((int) $employee['id'], $assignment, $attendanceSummary, $settings, $payrollMonthId);
            $deductions     = $this->deductionsService->compute($employee, $earnings, $attendanceSummary, $settings, $year, $month);

            $netSalary = max(0, round($earnings['gross_earnings'] - $deductions['gross_deductions'], 2));

            $settlementType = in_array($employee['status'], ['active', 'probation', 'notice_period', 'suspended'], true) ? 'regular' : 'final';

            $itemModel->insert([
                'payroll_run_id'       => $runId,
                'payroll_month_id'     => $payrollMonthId,
                'employee_id'          => $employee['id'],
                'salary_structure_id'  => $assignment['salary_structure_id'],
                'settlement_type'      => $settlementType,
                'working_days'         => $attendanceSummary['working_days'],
                'present_days'         => $attendanceSummary['present_days'],
                'paid_leave_days'      => $attendanceSummary['paid_leave_days'],
                'lop_days'             => $attendanceSummary['lop_days'],
                'half_days'            => $attendanceSummary['half_days'],
                'overtime_hours'       => $attendanceSummary['overtime_hours'],
                'overtime_amount'      => $earnings['overtime_amount'],
                'gross_earnings'       => $earnings['gross_earnings'],
                'gross_deductions'     => $deductions['gross_deductions'],
                'net_salary'           => $netSalary,
                'pf_employee'          => $deductions['pf']['employee'],
                'pf_employer'          => $deductions['pf']['employer'],
                'esi_employee'         => $deductions['esi']['employee'],
                'esi_employer'         => $deductions['esi']['employer'],
                'professional_tax'     => $deductions['professional_tax'],
                'tds'                  => $deductions['tds'],
                'loan_deduction'       => $deductions['loan_deduction'],
                'advance_deduction'    => $deductions['advance_deduction'],
                'bonus_amount'         => $earnings['bonus_amount'],
                'incentive_amount'     => $earnings['incentive_amount'],
                'reimbursement_amount' => $earnings['reimbursement_amount'],
                'arrears_amount'       => $earnings['arrears_amount'],
                'earnings_breakdown'   => json_encode(['items' => $earnings['items'], 'source_ids' => $earnings['source_ids']]),
                'deductions_breakdown' => json_encode($deductions),
                'status'               => 'draft',
                'created_by'           => session('tenant_user_id'),
            ], true);

            $totals['gross'] += $earnings['gross_earnings'];
            $totals['net']   += $netSalary;
            $totals['pf']    += $deductions['pf']['employee'];
            $totals['esi']   += $deductions['esi']['employee'];
            $totals['pt']    += $deductions['professional_tax'];
            $totals['tds']   += $deductions['tds'];
            $processed++;
        }

        $runModel->update($runId, [
            'status'          => 'generated',
            'total_employees' => $processed,
            'total_gross'     => round($totals['gross'], 2),
            'total_net'       => round($totals['net'], 2),
            'total_pf'        => round($totals['pf'], 2),
            'total_esi'       => round($totals['esi'], 2),
            'total_pt'        => round($totals['pt'], 2),
            'total_tds'       => round($totals['tds'], 2),
        ]);
        (new PayrollMonthModel($db))->update($payrollMonth['id'], ['status' => 'generated']);

        $this->audit->log('payroll_generate', 'payroll', 'payroll_run', $runId, null, ['month' => $month, 'year' => $year, 'processed' => $processed, 'skipped' => count($skipped)]);

        $period = sprintf('%04d-%02d', $year, $month);
        foreach ($this->notifications->usersWithPermission('payroll.approve') as $hrUserId) {
            $this->notifications->payrollGenerated($hrUserId, $processed, $period);
        }

        return ['run' => $runModel->find($runId), 'processed' => $processed, 'skipped' => $skipped];
    }

    /** Only a draft/generated (not-yet-approved) run may be cancelled — its items are discarded so the month can be regenerated cleanly. */
    public function cancel(int $runId): void
    {
        $db  = service('tenantContext')->db();
        $run = (new PayrollRunModel($db))->find($runId);
        if (! $run) {
            throw new RuntimeException('Payroll run not found.');
        }
        if (! in_array($run['status'], ['draft', 'generated'], true)) {
            throw new RuntimeException('Only a draft or generated run can be cancelled.');
        }

        $db->table('payroll_run_items')->where('payroll_run_id', $runId)->delete();
        (new PayrollRunModel($db))->update($runId, ['status' => 'cancelled']);
        (new PayrollMonthModel($db))->update($run['payroll_month_id'], ['status' => 'draft']);
        $this->audit->log('payroll_cancel', 'payroll', 'payroll_run', $runId, $run, ['status' => 'cancelled']);
    }

    private function eligibleEmployees($db, string $startDate, string $endDate): array
    {
        $model = new EmployeeModel($db);

        $regular = $model->whereIn('status', ['active', 'probation', 'notice_period', 'suspended'])
            ->where('date_of_joining <=', $endDate)
            ->findAll();

        $relieved = (new EmployeeModel($db))->where('status', 'relieved')
            ->where('date_of_joining <=', $endDate)
            ->where('updated_at >=', $startDate . ' 00:00:00')
            ->where('updated_at <=', $endDate . ' 23:59:59')
            ->findAll();

        return array_merge($regular, $relieved);
    }

    /** payroll_start_day > payroll_end_day means the cycle crosses a month boundary (e.g. 26th to 25th); otherwise it's a plain calendar-month cycle. */
    private function periodFor(int $month, int $year, array $settings): array
    {
        $startDay = (int) $settings['payroll_start_day'];
        $endDay   = (int) $settings['payroll_end_day'];

        if ($startDay <= $endDay) {
            $daysInMonth = (int) date('t', strtotime(sprintf('%04d-%02d-01', $year, $month)));

            return [
                sprintf('%04d-%02d-%02d', $year, $month, min($startDay, $daysInMonth)),
                sprintf('%04d-%02d-%02d', $year, $month, min($endDay, $daysInMonth)),
            ];
        }

        $prevMonth = $month === 1 ? 12 : $month - 1;
        $prevYear  = $month === 1 ? $year - 1 : $year;
        $prevDays  = (int) date('t', strtotime(sprintf('%04d-%02d-01', $prevYear, $prevMonth)));
        $thisDays  = (int) date('t', strtotime(sprintf('%04d-%02d-01', $year, $month)));

        return [
            sprintf('%04d-%02d-%02d', $prevYear, $prevMonth, min($startDay, $prevDays)),
            sprintf('%04d-%02d-%02d', $year, $month, min($endDay, $thisDays)),
        ];
    }
}
