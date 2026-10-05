<?php

namespace App\Services;

use App\Models\PayrollLoanInstallmentModel;
use App\Models\PayrollLoanModel;
use RuntimeException;

/**
 * emi_amount is a fixed value HR enters (spec field), not derived from
 * principal+rate. Installments are generated with a reducing-balance
 * principal/interest split for display purposes (interest_component =
 * outstanding * monthly rate); the last installment absorbs any rounding
 * remainder so the schedule always sums exactly to principal_amount.
 * Actual balance/installment mutation only happens at payroll approval time
 * (see PayrollApprovalService) — generation only reads the next pending
 * installment, same "commit on approval" precedent as LeaveBalanceService.
 */
class PayrollLoanService
{
    private PayrollLoanModel $loans;
    private PayrollLoanInstallmentModel $installments;

    public function __construct(private AuditService $audit = new AuditService())
    {
        $this->loans        = new PayrollLoanModel(service('tenantContext')->db());
        $this->installments = new PayrollLoanInstallmentModel(service('tenantContext')->db());
    }

    public function create(array $data): int
    {
        $this->assertUniqueNumber($data['loan_number'], null);

        $data['outstanding_balance'] = $data['principal_amount'];
        $data['created_by']          = session('tenant_user_id');
        $id                          = $this->loans->insert($data, true);

        $this->generateInstallments($id, $data);
        $this->audit->log('loan_create', 'payroll', 'payroll_loan', $id, null, $data);

        return $id;
    }

    private function generateInstallments(int $loanId, array $loan): void
    {
        $monthlyRate = ((float) $loan['interest_rate']) / 12 / 100;
        $balance     = (float) $loan['principal_amount'];
        $month       = (int) $loan['start_month'];
        $year        = (int) $loan['start_year'];

        for ($i = 1; $i <= (int) $loan['tenure_months']; $i++) {
            $isLast    = $i === (int) $loan['tenure_months'];
            $interest  = round($balance * $monthlyRate, 2);
            $principal = $isLast ? $balance : min($balance, round((float) $loan['emi_amount'] - $interest, 2));
            $balance   = round($balance - $principal, 2);

            $this->installments->insert([
                'loan_id'             => $loanId,
                'installment_no'      => $i,
                'due_year'            => $year,
                'due_month'           => $month,
                'emi_amount'          => round($principal + $interest, 2),
                'principal_component' => $principal,
                'interest_component'  => $interest,
                'status'              => 'pending',
            ]);

            $month++;
            if ($month > 12) {
                $month = 1;
                $year++;
            }
        }
    }

    public function forEmployee(int $employeeId): array
    {
        return $this->loans->forEmployee($employeeId);
    }

    public function installments(int $loanId): array
    {
        return $this->installments->forLoan($loanId);
    }

    public function foreclose(int $id): void
    {
        $old = $this->loans->find($id);
        if (! $old) {
            throw new RuntimeException('Loan not found.');
        }

        $this->loans->update($id, ['status' => 'foreclosed', 'outstanding_balance' => 0]);
        $this->installments->where('loan_id', $id)->where('status', 'pending')->set(['status' => 'skipped'])->update();
        $this->audit->log('loan_foreclose', 'payroll', 'payroll_loan', $id, $old, ['status' => 'foreclosed']);
    }

    /** Read-only — the amount PayrollRunService should show as loan_deduction for this employee this period. */
    public function nextPendingInstallment(int $loanId, int $year, int $month): ?array
    {
        return $this->installments->nextPendingFor($loanId, $year, $month);
    }

    /** Called only from PayrollApprovalService::approve() — commits the installment and reduces the loan balance. */
    public function commitInstallment(int $installmentId, int $runItemId): void
    {
        $installment = $this->installments->find($installmentId);
        if (! $installment || $installment['status'] !== 'pending') {
            return;
        }

        $this->installments->update($installmentId, [
            'status' => 'deducted', 'payroll_run_item_id' => $runItemId, 'deducted_at' => date('Y-m-d H:i:s'),
        ]);

        $loan        = $this->loans->find((int) $installment['loan_id']);
        $newBalance  = max(0, round((float) $loan['outstanding_balance'] - (float) $installment['principal_component'], 2));
        $this->loans->update((int) $loan['id'], [
            'outstanding_balance' => $newBalance,
            'status'              => $newBalance <= 0 ? 'closed' : 'active',
        ]);
        $this->audit->log('loan_installment_deduct', 'payroll', 'payroll_loan_installment', $installmentId, $installment, ['status' => 'deducted', 'run_item_id' => $runItemId]);
    }

    private function assertUniqueNumber(string $loanNumber, ?int $ignoreId): void
    {
        $query = service('tenantContext')->db()->table('payroll_loans')->where('loan_number', $loanNumber)->where('deleted_at', null);
        if ($ignoreId !== null) {
            $query->where('id !=', $ignoreId);
        }
        if ($query->countAllResults() > 0) {
            throw new RuntimeException('A loan with this number already exists.');
        }
    }
}
