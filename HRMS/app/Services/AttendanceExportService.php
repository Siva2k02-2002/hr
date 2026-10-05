<?php

namespace App\Services;

use Dompdf\Dompdf;
use Dompdf\Options as DompdfOptions;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Generic report formatter shared by all 8 report types in
 * AttendanceReportsController — each report only builds its own
 * (columns, rows) pair; the xlsx/csv/pdf formatting logic lives here once,
 * same shape as Phase 4's EmployeeExportService.
 */
class AttendanceExportService
{
    /**
     * @param array<string,string> $columns key => header label
     * @param array<int,array<string,mixed>> $rows
     */
    public function toXlsx(array $columns, array $rows): string
    {
        $writer = new Xlsx($this->buildSheet($columns, $rows));

        return $this->captured(fn () => $writer->save('php://output'));
    }

    /**
     * Deliberately bypasses PhpSpreadsheet here — building a full Spreadsheet
     * cell-object graph just to immediately flatten it back to CSV wastes
     * memory proportional to row count for no benefit CSV needs. fputcsv()
     * into a php://temp stream keeps this report export usable on a large
     * dataset that would otherwise risk hitting the memory limit.
     */
    public function toCsv(array $columns, array $rows): string
    {
        $stream = fopen('php://temp', 'r+');
        fputcsv($stream, array_values($columns));

        foreach ($rows as $row) {
            $ordered = [];
            foreach (array_keys($columns) as $key) {
                $ordered[] = (string) ($row[$key] ?? '');
            }
            fputcsv($stream, $ordered);
        }

        rewind($stream);
        $csv = stream_get_contents($stream);
        fclose($stream);

        return $csv;
    }

    public function toPdf(string $title, array $columns, array $rows): string
    {
        $html = view('attendance/reports/_export_pdf', ['title' => $title, 'columns' => $columns, 'rows' => $rows]);

        $options = new DompdfOptions();
        $options->set('isRemoteEnabled', false);
        $dompdf = new Dompdf($options);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->loadHtml($html);
        $dompdf->render();

        return $dompdf->output();
    }

    private function buildSheet(array $columns, array $rows): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->fromArray(array_values($columns), null, 'A1');

        $r = 2;
        foreach ($rows as $row) {
            $ordered = [];
            foreach (array_keys($columns) as $key) {
                $ordered[] = (string) ($row[$key] ?? '');
            }
            $sheet->fromArray($ordered, null, 'A' . $r);
            $r++;
        }

        foreach (range('A', 'Z') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return $spreadsheet;
    }

    private function captured(callable $write): string
    {
        ob_start();
        $write();

        return ob_get_clean();
    }
}
