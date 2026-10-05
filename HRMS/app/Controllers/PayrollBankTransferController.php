<?php

namespace App\Controllers;

use App\Services\PayrollBankTransferService;
use CodeIgniter\Exceptions\PageNotFoundException;

class PayrollBankTransferController extends BaseController
{
    public function export($runId, $format)
    {
        if (! in_array($format, ['xlsx', 'csv', 'pdf'], true)) {
            throw PageNotFoundException::forPageNotFound();
        }

        [$content, $mime, $ext] = (new PayrollBankTransferService())->export((int) $runId, $format);

        return $this->response
            ->setHeader('Content-Type', $mime)
            ->setHeader('Content-Disposition', 'attachment; filename="bank_transfer_run_' . $runId . '.' . $ext . '"')
            ->setBody($content);
    }
}
