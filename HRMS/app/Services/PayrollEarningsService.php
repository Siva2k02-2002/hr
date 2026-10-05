<?php

namespace App\Services;

use App\Models\PayrollArrearsModel;
use App\Models\PayrollBonusModel;
use App\Models\PayrollIncentiveModel;
use App\Models\PayrollReimbursementModel;
use App\Models\PayrollSalaryStructureItemModel;

/**
 * Computes every earning for one employee's payroll_run_items row. LOP is
 * deliberately NOT applied here — earnings are the full monthly structure
 * amount; PayrollDeductionsService/PayrollLopService subtracts LOP as its
 * own deduction line, matching the spec's Deduction Engine section rather
 * than prorating earnings directly.
 */
class PayrollEarningsService
{
    public function __construct(
        private SalaryStructureService $structureService = new SalaryStructureService(),
        private PayrollOvertimeService $overtime = new PayrollOvertimeService()
    ) {
    }

    /** @return array{items: array, gross_earnings: float, overtime_amount: float, bonus_amount: float, incentive_amount: float, reimbursement_amount: float, arrears_amount: float} */
    public function compute(int $employeeId, array $assignment, array $attendanceSummary, array $settings, ?int $payrollMonthId): array
    {
        $db = service('tenantContext')->db();

        $structureItems = (new PayrollSalaryStructureItemModel($db))->forStructure((int) $assignment['salary_structure_id']);
        $result         = $this->structureService->calculate($structureItems, (float) $assignment['gross_salary']);

        $basicAmount = 0.0;
        foreach ($result['items'] as $item) {
            if ($item['component_code'] === 'BASIC') {
                $basicAmount = (float) $item['amount'];
            }
        }
        $overtimeBasis  = $settings['overtime_rate_basis'] === 'gross' ? (float) $assignment['gross_salary'] : ($basicAmount ?: (float) $assignment['gross_salary']);
        $overtimeAmount = ((int) $settings['overtime_enabled']) === 1
            ? $this->overtime->calculate($overtimeBasis, (float) $attendanceSummary['working_days'], (float) $attendanceSummary['overtime_hours'], (float) $settings['overtime_multiplier'])
            : 0.0;

        $bonusRows         = (new PayrollBonusModel($db))->forEmployeeAndMonthOrUnassigned($employeeId, $payrollMonthId);
        $incentiveRows     = (new PayrollIncentiveModel($db))->forEmployeeAndMonthOrUnassigned($employeeId, $payrollMonthId);
        $reimbursementRows = (new PayrollReimbursementModel($db))->approvedUnassignedFor($employeeId);
        $arrearsRows       = (new PayrollArrearsModel($db))->forEmployeeAndMonthOrUnassigned($employeeId, $payrollMonthId);

        $bonusAmount         = (float) array_sum(array_column($bonusRows, 'amount'));
        $incentiveAmount     = (float) array_sum(array_column($incentiveRows, 'amount'));
        $reimbursementAmount = (float) array_sum(array_column($reimbursementRows, 'amount'));
        $arrearsAmount       = (float) array_sum(array_column($arrearsRows, 'amount'));

        $grossEarnings = $result['gross_earnings'] + $overtimeAmount + $bonusAmount + $incentiveAmount + $reimbursementAmount + $arrearsAmount;

        // PT/TDS must be computed on taxable income, not total gross (audit finding PAY-01):
        // reimbursements are an expense repayment, not income, and each structure component
        // carries its own is_taxable flag (e.g. certain conveyance/LTA allowances are exempt)
        // that was being ignored — every earning was silently treated as taxable.
        $taxableStructureEarnings = array_sum(array_map(
            static fn ($item) => ($item['component_type'] === 'earning' && $item['is_taxable']) ? (float) $item['amount'] : 0.0,
            $result['items']
        ));
        $taxableGross = $taxableStructureEarnings + $overtimeAmount + $bonusAmount + $incentiveAmount + $arrearsAmount;

        return [
            'items'                     => $result['items'],
            'structure_gross_earnings'  => $result['gross_earnings'],
            'gross_earnings'            => round($grossEarnings, 2),
            'taxable_gross'        => round($taxableGross, 2),
            'overtime_amount'      => $overtimeAmount,
            'bonus_amount'         => $bonusAmount,
            'incentive_amount'     => $incentiveAmount,
            'reimbursement_amount' => $reimbursementAmount,
            'arrears_amount'       => $arrearsAmount,
            // Row ids consumed — PayrollApprovalService flips these to 'paid'/assigns payroll_month_id only on approval, never at draft generation.
            'source_ids'           => [
                'bonus'         => array_column($bonusRows, 'id'),
                'incentive'     => array_column($incentiveRows, 'id'),
                'reimbursement' => array_column($reimbursementRows, 'id'),
                'arrears'       => array_column($arrearsRows, 'id'),
            ],
        ];
    }
}
