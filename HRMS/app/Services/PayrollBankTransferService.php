<?php

namespace App\Services;

use RuntimeException;

/** Columns are deliberately a fixed, well-known set for now — "configurable columns" per the spec means which of these appear/their order, not arbitrary custom fields; add to $ALL_COLUMNS to support a new bank format later. */
class PayrollBankTransferService
{
    private const ALL_COLUMNS = [
        'employee_code'  => 'Employee Code',
        'employee_name'  => 'Employee Name',
        'bank_name'      => 'Bank Name',
        'account_number' => 'Account Number',
        'ifsc_code'      => 'IFSC Code',
        'net_salary'     => 'Net Salary',
    ];

    public function __construct(private PayrollExportService $export = new PayrollExportService())
    {
    }

    public function rowsForRun(int $runId): array
    {
        $rows = service('tenantContext')->db()->table('payroll_run_items pri')
            ->select("e.employee_code, CONCAT(e.first_name,' ',e.last_name) as employee_name, b.bank_name, b.account_number, b.ifsc_code, pri.net_salary")
            ->join('employees e', 'e.id = pri.employee_id')
            ->join('employee_bank_accounts b', 'b.employee_id = pri.employee_id AND b.is_primary = 1', 'left')
            ->where('pri.payroll_run_id', $runId)
            ->whereIn('pri.status', ['approved', 'locked', 'paid'])
            ->orderBy('e.first_name')
            ->get()->getResultArray();

        return $rows;
    }

    /** @return array{0: string, 1: string, 2: string} content, mime, extension */
    public function export(int $runId, string $format, array $columns = []): array
    {
        $columns = $columns === [] ? self::ALL_COLUMNS : array_intersect_key(self::ALL_COLUMNS, array_flip($columns));
        $rows    = $this->rowsForRun($runId);

        return match ($format) {
            'xlsx' => [$this->export->toXlsx($columns, $rows), 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'xlsx'],
            'csv'  => [$this->export->toCsv($columns, $rows), 'text/csv', 'csv'],
            'pdf'  => [$this->export->toPdf('Bank Transfer File', $columns, $rows), 'application/pdf', 'pdf'],
            default => throw new RuntimeException('Unsupported export format.'),
        };
    }
}
