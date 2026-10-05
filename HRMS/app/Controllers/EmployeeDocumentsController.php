<?php

namespace App\Controllers;

use App\Models\EmployeeDocumentModel;
use App\Services\EmployeeDocumentService;
use CodeIgniter\Exceptions\PageNotFoundException;
use RuntimeException;

class EmployeeDocumentsController extends BaseController
{
    public function store($employeeId)
    {
        $file = $this->request->getFile('file');
        if (! $file) {
            return redirect()->to(site_url('employees/' . $employeeId . '?tab=documents'))->with('error', 'Please choose a file to upload.');
        }

        try {
            (new EmployeeDocumentService())->upload(
                (int) $employeeId,
                $file,
                (string) $this->request->getPost('document_type'),
                (string) $this->request->getPost('document_number') ?: null,
                (string) $this->request->getPost('expiry_date') ?: null,
                (string) $this->request->getPost('remarks') ?: null
            );
        } catch (RuntimeException $e) {
            return redirect()->to(site_url('employees/' . $employeeId . '?tab=documents'))->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('employees/' . $employeeId . '?tab=documents'))->with('success', 'Document uploaded.');
    }

    public function delete($employeeId, $id)
    {
        try {
            (new EmployeeDocumentService())->delete((int) $id);
        } catch (RuntimeException $e) {
            return redirect()->to(site_url('employees/' . $employeeId . '?tab=documents'))->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('employees/' . $employeeId . '?tab=documents'))->with('success', 'Document removed.');
    }

    /**
     * The only path a document is ever reachable from — permission-gated by
     * the 'employee.documents' route filter, and the file itself lives
     * outside the public webroot (see EmployeeDocumentService).
     */
    public function download($employeeId, $id)
    {
        $doc = (new EmployeeDocumentModel(service('tenantContext')->db()))->where('employee_id', $employeeId)->find($id);
        if (! $doc) {
            throw PageNotFoundException::forPageNotFound();
        }

        $absolutePath = WRITEPATH . 'uploads/' . $doc['file_path'];
        if (! is_file($absolutePath)) {
            throw PageNotFoundException::forPageNotFound();
        }

        $forceDownload = (bool) $this->request->getGet('download');

        return $this->response
            ->setHeader('Content-Type', $doc['mime_type'])
            ->setHeader('Content-Disposition', ($forceDownload ? 'attachment' : 'inline') . '; filename="' . $this->safeFilename($doc['original_filename']) . '"')
            ->setHeader('X-Content-Type-Options', 'nosniff')
            ->setBody(file_get_contents($absolutePath));
    }

    /** original_filename is the uploader's own client-supplied name — strip quote/CRLF/control chars before it goes into a response header. */
    private function safeFilename(string $name): string
    {
        $name = str_replace(['"', "\r", "\n"], '', $name);

        return preg_replace('/[\x00-\x1F\x7F]/', '', $name);
    }
}
