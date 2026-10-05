<?php

namespace App\Models;

use CodeIgniter\Model;

class PayrollLoanInstallmentModel extends Model
{
    protected $table         = 'payroll_loan_installments';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'loan_id', 'installment_no', 'due_year', 'due_month', 'emi_amount', 'principal_component',
        'interest_component', 'status', 'payroll_run_item_id', 'deducted_at',
    ];

    public function forLoan(int $loanId): array
    {
        return $this->where('loan_id', $loanId)->orderBy('installment_no')->findAll();
    }

    /** The next pending installment due on/before $year/$month for $loanId — what PayrollDeductionsService deducts for a given run. */
    public function nextPendingFor(int $loanId, int $year, int $month): ?array
    {
        return $this->where('loan_id', $loanId)
            ->where('status', 'pending')
            ->groupStart()
                ->where('due_year <', $year)
                ->orGroupStart()->where('due_year', $year)->where('due_month <=', $month)->groupEnd()
            ->groupEnd()
            ->orderBy('due_year')->orderBy('due_month')
            ->first();
    }
}
