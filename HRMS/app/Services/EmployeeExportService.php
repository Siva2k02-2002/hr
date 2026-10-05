<?php

namespace App\Services;

use Dompdf\Dompdf;
use Dompdf\Options as DompdfOptions;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Takes whatever row set the caller already filtered (EmployeesController
 * builds the same query the list page used, just without pagination) — this
 * service only formats it, so "respects current filters" is automatic.
 */
class EmployeeExportService
{
    private const COLUMNS = [
        'employee_code'   => 'Employee Code',
        'full_name'       => 'Name',
        'branch_name'     => 'Branch',
        'department_name' => 'Department',
        'designation_name'=> 'Designation',
        'manager_name'    => 'Manager',
        'mobile'          => 'Mobile',
        'company_email'   => 'Company Email',
        'date_of_joining' => 'Joining Date',
        'status'          => 'Status',
    ];

    public function toXlsx(array $employees): string
    {
        $sheet = $this->buildSheet($employees);
        $writer = new Xlsx($sheet);

        return $this->captured(fn () => $writer->save('php://output'));
    }

    /** Bypasses PhpSpreadsheet — see AttendanceExportService::toCsv() for why. */
    public function toCsv(array $employees): string
    {
        $stream = fopen('php://temp', 'r+');
        fputcsv($stream, array_values(self::COLUMNS));

        foreach ($employees as $employee) {
            fputcsv($stream, array_values($this->rowFor($employee)));
        }

        rewind($stream);
        $csv = stream_get_contents($stream);
        fclose($stream);

        return $csv;
    }

    public function toPdf(array $employees): string
    {
        $rows = array_map([$this, 'rowFor'], $employees);
        $html = view('employees/_export_pdf', ['rows' => $rows, 'columns' => self::COLUMNS]);

        $options = new DompdfOptions();
        $options->set('isRemoteEnabled', false);
        $dompdf = new Dompdf($options);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->loadHtml($html);
        $dompdf->render();

        return $dompdf->output();
    }

    private function buildSheet(array $employees): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->fromArray(array_values(self::COLUMNS), null, 'A1');

        $r = 2;
        foreach ($employees as $employee) {
            $row = $this->rowFor($employee);
            $sheet->fromArray(array_values($row), null, 'A' . $r);
            $r++;
        }

        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return $spreadsheet;
    }

    private function rowFor(array $employee): array
    {
        $out = [];
        foreach (self::COLUMNS as $key => $label) {
            $out[$key] = $key === 'full_name'
                ? implode(' ', array_filter([$employee['first_name'], $employee['middle_name'] ?? null, $employee['last_name']]))
                : (string) ($employee[$key] ?? '');
        }
        if (isset($out['status'])) {
            $out['status'] = employee_status_label($out['status']);
        }

        return $out;
    }

    private function captured(callable $write): string
    {
        ob_start();
        $write();

        return ob_get_clean();
    }
}
