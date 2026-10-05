<?php

namespace App\Controllers;

use App\Services\AttendanceImportService;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use RuntimeException;
use Throwable;

/**
 * Preview and commit are two separate requests (the user reviews the validation
 * report before anything is written) — the parsed+validated rows are held in
 * session between them, cleared once commit() runs. Mirrors EmployeeImportController.
 */
class AttendanceImportController extends BaseController
{
    public function form()
    {
        return view('attendance/import', ['title' => 'Attendance Excel Import']);
    }

    public function template()
    {
        $writer = new Xlsx((new AttendanceImportService())->template());

        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();

        return $this->response
            ->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->setHeader('Content-Disposition', 'attachment; filename="attendance_import_template.xlsx"')
            ->setBody($content);
    }

    public function preview()
    {
        if ($this->rateLimited('attendance_import', 10, MINUTE)) {
            return redirect()->to(site_url('attendance/import'))->with('error', 'Too many import attempts. Please slow down.');
        }

        $file = $this->request->getFile('file');
        if (! $file || ! $file->isValid()) {
            return redirect()->to(site_url('attendance/import'))->with('error', 'Please choose a valid .xlsx or .csv file.');
        }

        $ext = strtolower($file->getClientExtension());
        if (! in_array($ext, ['xlsx', 'xls', 'csv'], true)) {
            return redirect()->to(site_url('attendance/import'))->with('error', 'Only .xlsx, .xls, or .csv files are supported.');
        }

        if ($file->getSize() > 10 * 1024 * 1024) {
            return redirect()->to(site_url('attendance/import'))->with('error', 'File must be 10MB or smaller.');
        }

        try {
            $service = new AttendanceImportService();
            $rows    = $service->parse($file->getTempName());
            $result  = $service->validate($rows);
        } catch (Throwable $e) {
            return redirect()->to(site_url('attendance/import'))->with('error', 'Could not read that file: ' . $e->getMessage());
        }

        session()->set('attendance_import_preview', $result['rows']);

        return view('attendance/import_preview', [
            'title'          => 'Attendance Import Preview',
            'rows'           => $result['rows'],
            'validCount'     => $result['validCount'],
            'errorCount'     => $result['errorCount'],
            'existingCount'  => $result['existingCount'],
            'duplicateCount' => $result['duplicateCount'],
            'totalCount'     => count($result['rows']),
        ]);
    }

    public function commit()
    {
        $rows = session('attendance_import_preview');
        if (! $rows) {
            return redirect()->to(site_url('attendance/import'))->with('error', 'Your import preview expired — please upload the file again.');
        }

        try {
            $result = (new AttendanceImportService())->commit($rows);
        } catch (RuntimeException $e) {
            return redirect()->to(site_url('attendance/import'))->with('error', $e->getMessage());
        }

        session()->remove('attendance_import_preview');

        return redirect()->to(site_url('attendance'))
            ->with('success', "Import complete: {$result['imported']} records saved, {$result['skipped']} skipped.");
    }
}
