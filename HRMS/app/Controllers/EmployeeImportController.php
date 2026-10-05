<?php

namespace App\Controllers;

use App\Services\EmployeeImportService;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use RuntimeException;
use Throwable;

/**
 * Preview and commit are two separate requests (the user reviews the
 * validation report before anything is written) — the parsed+validated
 * rows are held in session between them, cleared once commit() runs.
 */
class EmployeeImportController extends BaseController
{
    public function form()
    {
        return view('employees/import', ['title' => 'Import Employees']);
    }

    public function template()
    {
        $writer = new Xlsx((new EmployeeImportService())->template());

        return $this->streamXlsx($writer, 'employee_import_template.xlsx');
    }

    public function preview()
    {
        if ($this->rateLimited('employee_import', 10, MINUTE)) {
            return redirect()->to(site_url('employees/import'))->with('error', 'Too many import attempts. Please slow down.');
        }

        $file = $this->request->getFile('file');
        if (! $file || ! $file->isValid()) {
            return redirect()->to(site_url('employees/import'))->with('error', 'Please choose a valid .xlsx or .csv file.');
        }

        $ext = strtolower($file->getClientExtension());
        if (! in_array($ext, ['xlsx', 'xls', 'csv'], true)) {
            return redirect()->to(site_url('employees/import'))->with('error', 'Only .xlsx, .xls, or .csv files are supported.');
        }

        if ($file->getSize() > 10 * 1024 * 1024) {
            return redirect()->to(site_url('employees/import'))->with('error', 'File must be 10MB or smaller.');
        }

        try {
            $service = new EmployeeImportService();
            $rows    = $service->parse($file->getTempName());
            $result  = $service->validate($rows);
        } catch (Throwable $e) {
            return redirect()->to(site_url('employees/import'))->with('error', 'Could not read that file: ' . $e->getMessage());
        }

        session()->set('employee_import_preview', $result['rows']);

        return view('employees/import_preview', [
            'title'          => 'Import Preview',
            'rows'           => $result['rows'],
            'validCount'     => $result['validCount'],
            'errorCount'     => $result['errorCount'],
            'duplicateCount' => $result['duplicateCount'],
        ]);
    }

    public function commit()
    {
        $rows = session('employee_import_preview');
        if (! $rows) {
            return redirect()->to(site_url('employees/import'))->with('error', 'Your import preview expired — please upload the file again.');
        }

        $skipDuplicates = (bool) $this->request->getPost('skip_duplicates');

        try {
            $result = (new EmployeeImportService())->commit($rows, $skipDuplicates);
        } catch (RuntimeException $e) {
            return redirect()->to(site_url('employees/import'))->with('error', $e->getMessage());
        }

        session()->remove('employee_import_preview');

        return redirect()->to(site_url('employees'))
            ->with('success', "Import complete: {$result['inserted']} added, {$result['skipped']} skipped.");
    }

    private function streamXlsx(Xlsx $writer, string $filename)
    {
        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();

        return $this->response
            ->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($content);
    }
}
