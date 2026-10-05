<?php

namespace App\Services;

use App\Models\PayrollReimbursementModel;
use CodeIgniter\HTTP\Files\UploadedFile;
use RuntimeException;

/** File upload follows the same pattern as EmployeeDocumentService — stored under writable/uploads, never in webroot. */
class PayrollReimbursementService
{
    private const UPLOAD_PATH = WRITEPATH . 'uploads/reimbursements/';
    private const ALLOWED_MIME = [
        'application/pdf' => 'pdf',
        'image/jpeg'       => 'jpg',
        'image/png'        => 'png',
        'image/webp'       => 'webp',
    ];
    private const MAX_BYTES = 10 * 1024 * 1024;

    private PayrollReimbursementModel $reimbursements;

    public function __construct(private AuditService $audit = new AuditService())
    {
        $this->reimbursements = new PayrollReimbursementModel(service('tenantContext')->db());
    }

    public function create(array $data, ?UploadedFile $file): int
    {
        if ($file && $file->isValid() && ! $file->hasMoved()) {
            $data['attachment_path'] = $this->store($file);
        }

        $data['created_by'] = session('tenant_user_id');
        $id                 = $this->reimbursements->insert($data, true);
        $this->audit->log('reimbursement_create', 'payroll', 'payroll_reimbursement', $id, null, $data);

        return $id;
    }

    private function store(UploadedFile $file): string
    {
        if ($file->getSize() > self::MAX_BYTES) {
            throw new RuntimeException('Receipt must be 10MB or smaller.');
        }

        $realMime = (new \finfo(FILEINFO_MIME_TYPE))->file($file->getTempName());
        if (! isset(self::ALLOWED_MIME[$realMime])) {
            throw new RuntimeException('Unsupported file type. Allowed: PDF, JPG, PNG, WEBP.');
        }

        if (! is_dir(self::UPLOAD_PATH) && ! mkdir(self::UPLOAD_PATH, 0750, true) && ! is_dir(self::UPLOAD_PATH)) {
            throw new RuntimeException('Could not create the upload directory.');
        }

        $name = bin2hex(random_bytes(16)) . '.' . self::ALLOWED_MIME[$realMime];
        $file->move(self::UPLOAD_PATH, $name);

        return $name;
    }

    public function approve(int $id, int $approvedBy): void
    {
        $old = $this->reimbursements->find($id);
        if (! $old) {
            throw new RuntimeException('Reimbursement not found.');
        }
        if ($old['status'] !== 'pending') {
            throw new RuntimeException('Only a pending reimbursement can be approved.');
        }

        $new = ['status' => 'approved', 'approved_by' => $approvedBy, 'approved_at' => date('Y-m-d H:i:s')];
        $this->reimbursements->update($id, $new);
        $this->audit->log('reimbursement_approve', 'payroll', 'payroll_reimbursement', $id, $old, $new);
    }

    public function reject(int $id, int $approvedBy, ?string $remarks): void
    {
        $old = $this->reimbursements->find($id);
        if (! $old) {
            throw new RuntimeException('Reimbursement not found.');
        }
        if ($old['status'] !== 'pending') {
            throw new RuntimeException('Only a pending reimbursement can be rejected.');
        }

        $new = ['status' => 'rejected', 'approved_by' => $approvedBy, 'approved_at' => date('Y-m-d H:i:s'), 'remarks' => $remarks];
        $this->reimbursements->update($id, $new);
        $this->audit->log('reimbursement_reject', 'payroll', 'payroll_reimbursement', $id, $old, $new);
    }

    public function attachmentPath(int $id): ?string
    {
        $row = $this->reimbursements->find($id);

        return $row && $row['attachment_path'] ? self::UPLOAD_PATH . $row['attachment_path'] : null;
    }
}
