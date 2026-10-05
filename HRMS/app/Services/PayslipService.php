<?php

namespace App\Services;

use App\Models\EmployeeBankAccountModel;
use App\Models\EmployeeModel;
use App\Models\PayrollAdjustmentModel;
use App\Models\PayrollPayslipModel;
use App\Models\PayrollRunItemModel;
use App\Models\PayrollSettingModel;
use Dompdf\Dompdf;
use Dompdf\Options as DompdfOptions;
use RuntimeException;

/** PDF is rendered on demand — payroll_payslips only stores the number and a download audit trail, never the file itself. */
class PayslipService
{
    public function __construct(private AuditService $audit = new AuditService())
    {
    }

    public function ensurePayslip(int $runItemId): array
    {
        $db      = service('tenantContext')->db();
        $model   = new PayrollPayslipModel($db);
        $existing = $model->forRunItem($runItemId);
        if ($existing) {
            return $existing;
        }

        $item     = (new PayrollRunItemModel($db))->find($runItemId);
        if (! $item) {
            throw new RuntimeException('Payroll line not found.');
        }

        $prefix = (new PayrollSettingModel($db))->current()['payslip_prefix'];
        $employee = (new EmployeeModel($db))->find($item['employee_id']);
        $number   = sprintf('%s-%04d-%02d-%s', $prefix, $item['payroll_month_id'] ? $this->yearFor($db, (int) $item['payroll_month_id']) : (int) date('Y'), $this->monthFor($db, (int) $item['payroll_month_id']), $employee['employee_code']);

        $id = $model->insert([
            'payroll_run_item_id' => $runItemId,
            'employee_id'         => $item['employee_id'],
            'payroll_month_id'    => $item['payroll_month_id'],
            'payslip_number'      => $number,
            'generated_at'        => date('Y-m-d H:i:s'),
        ], true);

        return $model->find($id);
    }

    private function yearFor($db, int $payrollMonthId): int
    {
        return (int) ($db->table('payroll_months')->select('year')->where('id', $payrollMonthId)->get()->getRowArray()['year'] ?? date('Y'));
    }

    private function monthFor($db, int $payrollMonthId): int
    {
        return (int) ($db->table('payroll_months')->select('month')->where('id', $payrollMonthId)->get()->getRowArray()['month'] ?? date('n'));
    }

    public function renderPdf(int $runItemId): string
    {
        $db   = service('tenantContext')->db();
        $item = $db->table('payroll_run_items pri')
            ->select('pri.*, m.month, m.year')
            ->join('payroll_months m', 'm.id = pri.payroll_month_id')
            ->where('pri.id', $runItemId)
            ->get()->getRowArray();

        if (! $item) {
            throw new RuntimeException('Payroll line not found.');
        }

        $employee    = (new EmployeeModel($db))->withRelations()->find($item['employee_id']);
        $bank        = (new EmployeeBankAccountModel($db))->primaryFor((int) $item['employee_id']);
        $settings    = (new PayrollSettingModel($db))->current();
        $payslip     = $this->ensurePayslip($runItemId);
        $earnings    = json_decode((string) $item['earnings_breakdown'], true) ?: ['items' => []];
        $deductions  = json_decode((string) $item['deductions_breakdown'], true) ?: [];
        $adjustments = (new PayrollAdjustmentModel($db))->forRunItem($runItemId);

        $html = view('payroll/payslips/_pdf', [
            'employee'    => $employee,
            'bank'        => $bank,
            'item'        => $item,
            'settings'    => $settings,
            'payslip'     => $payslip,
            'earnings'    => $earnings,
            'deductions'  => $deductions,
            'adjustments' => $adjustments,
        ]);

        $options = new DompdfOptions();
        $options->set('isRemoteEnabled', false);
        $dompdf = new Dompdf($options);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->loadHtml($html);
        $dompdf->render();

        return $dompdf->output();
    }

    public function recordDownload(int $payslipId): void
    {
        $db    = service('tenantContext')->db();
        $model = new PayrollPayslipModel($db);
        $row   = $model->find($payslipId);
        if (! $row) {
            return;
        }

        $model->update($payslipId, ['downloaded_at' => date('Y-m-d H:i:s'), 'downloaded_count' => (int) $row['downloaded_count'] + 1]);
        $this->audit->log('payslip_download', 'payroll', 'payroll_payslip', $payslipId, null, ['downloaded_count' => (int) $row['downloaded_count'] + 1]);
    }
}
