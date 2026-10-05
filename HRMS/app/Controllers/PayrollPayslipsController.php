<?php

namespace App\Controllers;

use App\Models\PayrollRunItemModel;
use App\Services\PayslipService;
use CodeIgniter\Exceptions\PageNotFoundException;
use ZipArchive;

class PayrollPayslipsController extends BaseController
{
    public function index()
    {
        $filters = [
            'q'      => (string) $this->request->getGet('q'),
            'month'  => (string) $this->request->getGet('month'),
            'year'   => (string) $this->request->getGet('year'),
        ];

        $model = (new PayrollRunItemModel(service('tenantContext')->db()))
            ->select("payroll_run_items.*, e.employee_code, CONCAT(e.first_name,' ',e.last_name) as employee_name, m.month, m.year")
            ->join('employees e', 'e.id = payroll_run_items.employee_id')
            ->join('payroll_months m', 'm.id = payroll_run_items.payroll_month_id')
            ->whereIn('payroll_run_items.status', ['approved', 'locked', 'paid']);

        if ($filters['q'] !== '') {
            $model->groupStart()->like('e.first_name', $filters['q'])->orLike('e.last_name', $filters['q'])->orLike('e.employee_code', $filters['q'])->groupEnd();
        }
        if ($filters['month'] !== '') {
            $model->where('m.month', $filters['month']);
        }
        if ($filters['year'] !== '') {
            $model->where('m.year', $filters['year']);
        }

        $items = $model->orderBy('m.year', 'DESC')->orderBy('m.month', 'DESC')->paginate(20, 'payslips');

        return view('payroll/payslips/index', ['title' => 'Payslips', 'items' => $items, 'pager' => $model->pager, 'filters' => $filters]);
    }

    public function download($runItemId)
    {
        $service = new PayslipService();
        $payslip = $service->ensurePayslip((int) $runItemId);
        $pdf     = $service->renderPdf((int) $runItemId);
        $service->recordDownload((int) $payslip['id']);

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $payslip['payslip_number'] . '.pdf"')
            ->setBody($pdf);
    }

    public function bulkZip($runId)
    {
        $items = (new PayrollRunItemModel(service('tenantContext')->db()))->forRun((int) $runId);
        if ($items === []) {
            throw PageNotFoundException::forPageNotFound();
        }

        $service = new PayslipService();
        $tmpFile = WRITEPATH . 'uploads/payslips_' . $runId . '_' . uniqid() . '.zip';

        $zip = new ZipArchive();
        $zip->open($tmpFile, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        foreach ($items as $item) {
            $payslip = $service->ensurePayslip((int) $item['id']);
            $zip->addFromString($payslip['payslip_number'] . '.pdf', $service->renderPdf((int) $item['id']));
        }
        $zip->close();

        $contents = file_get_contents($tmpFile);
        unlink($tmpFile);

        return $this->response->download('payslips_run_' . $runId . '.zip', $contents);
    }
}
