<?php

namespace App\Controllers;

use App\Models\EmployeeModel;
use App\Services\AuditService;
use App\Services\EmployeeExportService;

/** Builds the same filtered query the list page (EmployeesController::index) used, just without pagination — see EmployeeModel::applyFilters(). */
class EmployeeExportController extends BaseController
{
    public function excel()
    {
        return $this->export('xlsx');
    }

    public function csv()
    {
        return $this->export('csv');
    }

    public function pdf()
    {
        return $this->export('pdf');
    }

    private function export(string $format)
    {
        $model = (new EmployeeModel(service('tenantContext')->db()))->withRelations();
        $model->applyFilters($this->request->getGet() ?? []);
        $employees = $model->orderBy('employees.first_name', 'ASC')->findAll();

        $service  = new EmployeeExportService();
        $filename = 'employees_' . date('Y-m-d');

        [$content, $mime, $ext] = match ($format) {
            'xlsx'  => [$service->toXlsx($employees), 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'xlsx'],
            'csv'   => [$service->toCsv($employees), 'text/csv', 'csv'],
            'pdf'   => [$service->toPdf($employees), 'application/pdf', 'pdf'],
        };

        (new AuditService())->log('export', 'employees', 'employee', null, null, ['format' => $format, 'count' => count($employees)]);

        return $this->response
            ->setHeader('Content-Type', $mime)
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '.' . $ext . '"')
            ->setBody($content);
    }
}
