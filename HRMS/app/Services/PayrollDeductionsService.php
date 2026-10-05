<?php

namespace App\Services;

use App\Models\PayrollAdvanceModel;
use App\Models\PayrollLoanModel;

/**
 * Computes every deduction for one employee's payroll_run_items row. Loan/
 * advance amounts here are READ-ONLY previews of the next pending
 * installment/recovery — the actual balance/installment mutation only
 * happens in PayrollApprovalService::approve(), same "commit on approval"
 * precedent as LeaveBalanceService.
 */
class PayrollDeductionsService
{
    public function __construct(
        private PayrollPfService $pf = new PayrollPfService(),
        private PayrollEsiService $esi = new PayrollEsiService(),
        private PayrollProfessionalTaxService $pt = new PayrollProfessionalTaxService(),
        private PayrollTdsService $tds = new PayrollTdsService(),
        private PayrollLopService $lop = new PayrollLopService(),
        private PayrollLoanService $loanService = new PayrollLoanService()
    ) {
    }

    public function compute(array $employee, array $earnings, array $attendanceSummary, array $settings, int $year, int $month): array
    {
        $db = service('tenantContext')->db();

        $byCode = array_column($earnings['items'], 'amount', 'component_code');
        $basic  = $byCode['BASIC'] ?? 0.0;

        $pfWage  = $this->wageFor($earnings['items'], 'pf_applicable', $settings['pf_wage_basis'] ?? null, $basic, $earnings['structure_gross_earnings']);
        $esiWage = $this->wageFor($earnings['items'], 'esi_applicable', null, $basic, $earnings['structure_gross_earnings']);

        $pfResult  = ((int) $settings['pf_enabled']) === 1 ? $this->pf->calculate($pfWage) : ['wage' => 0, 'employee' => 0.0, 'employer' => 0.0];
        $esiResult = ((int) $settings['esi_enabled']) === 1 ? $this->esi->calculate($esiWage) : ['eligible' => false, 'employee' => 0.0, 'employer' => 0.0];

        $state = $db->table('branches')->select('state')->where('id', $employee['branch_id'])->get()->getRowArray()['state'] ?? null;
        // Taxable gross (structure earnings flagged is_taxable + overtime/bonus/incentive/arrears),
        // not the full gross_earnings — reimbursements and non-taxable components must not
        // inflate PT/TDS (audit finding PAY-01). See PayrollEarningsService::compute().
        $pt  = ((int) $settings['pt_enabled']) === 1 ? $this->pt->calculate($state, $earnings['taxable_gross']) : 0.0;
        $tds = ((int) $settings['tds_enabled']) === 1 ? $this->tds->calculate($earnings['taxable_gross']) : 0.0;

        $lopBasis = $settings['lop_deduction_basis'] === 'basic' ? ($basic ?: $earnings['structure_gross_earnings']) : $earnings['structure_gross_earnings'];
        $lopAmount = ((int) $settings['lop_enabled']) === 1
            ? $this->lop->calculate($lopBasis, (float) $attendanceSummary['working_days'], (float) $attendanceSummary['lop_days'])
            : 0.0;

        [$loanDeduction, $loanInstallmentIds] = $this->loanDeduction($db, (int) $employee['id'], $year, $month);
        [$advanceDeduction, $advanceIds]      = $this->advanceDeduction($db, (int) $employee['id']);

        $grossDeductions = $pfResult['employee'] + $esiResult['employee'] + $pt + $tds + $loanDeduction + $advanceDeduction + $lopAmount;

        return [
            'pf'                 => $pfResult,
            'esi'                => $esiResult,
            'professional_tax'   => $pt,
            'tds'                => $tds,
            'lop_amount'         => $lopAmount,
            'loan_deduction'     => $loanDeduction,
            'advance_deduction'  => $advanceDeduction,
            'gross_deductions'   => round($grossDeductions, 2),
            'source_ids'         => ['loan_installments' => $loanInstallmentIds, 'advances' => $advanceIds],
        ];
    }

    /** Sums components flagged for the given applicability field; falls back to a named basis (basic/basic_da/gross) when the structure has none flagged, so PF/ESI still compute something sensible for a bare-bones structure. */
    private function wageFor(array $items, string $flagField, ?string $fallbackBasis, float $basic, float $gross): float
    {
        $flagged = array_filter($items, static fn ($i) => ! empty($i[$flagField]));
        if ($flagged !== []) {
            return (float) array_sum(array_column($flagged, 'amount'));
        }

        return match ($fallbackBasis) {
            'basic_da' => $basic + (array_column($items, 'amount', 'component_code')['DA'] ?? 0.0),
            'gross'    => $gross,
            default    => $basic ?: $gross,
        };
    }

    /** @return array{0: float, 1: array<int>} */
    private function loanDeduction($db, int $employeeId, int $year, int $month): array
    {
        $loans  = (new PayrollLoanModel($db))->where('employee_id', $employeeId)->where('status', 'active')->findAll();
        $total  = 0.0;
        $ids    = [];

        foreach ($loans as $loan) {
            $installment = $this->loanService->nextPendingInstallment((int) $loan['id'], $year, $month);
            if ($installment) {
                $total += (float) $installment['emi_amount'];
                $ids[]  = (int) $installment['id'];
            }
        }

        return [round($total, 2), $ids];
    }

    /** @return array{0: float, 1: array<int, float>} advance_id => amount */
    private function advanceDeduction($db, int $employeeId): array
    {
        $advances = (new PayrollAdvanceModel($db))->where('employee_id', $employeeId)->where('status', 'active')->findAll();
        $advanceService = new PayrollAdvanceService();
        $total = 0.0;
        $byId  = [];

        foreach ($advances as $advance) {
            $amount = $advanceService->nextDeductionAmount($advance);
            if ($amount > 0) {
                $total += $amount;
                $byId[(int) $advance['id']] = $amount;
            }
        }

        return [round($total, 2), $byId];
    }
}
