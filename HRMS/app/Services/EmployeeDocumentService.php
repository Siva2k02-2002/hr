<?php

namespace App\Services;

use App\Models\EmployeeDocumentModel;
use CodeIgniter\HTTP\Files\UploadedFile;
use RuntimeException;

/**
 * MIME is sniffed from the real file bytes (finfo), never trusted from the
 * client's Content-Type header or filename extension — that alone rules out
 * disguised executable uploads. The stored filename is always server-
 * generated (random hex + an extension derived from the sniffed MIME, not
 * from the client's filename), which also closes the path-traversal angle
 * that CVE-2026-63222 was about. The original filename is kept only as a
 * display-only DB column, never used to build a filesystem path.
 */
class EmployeeDocumentService
{
    private const ALLOWED_MIME = [
        'application/pdf' => 'pdf',
        'image/jpeg'       => 'jpg',
        'image/png'        => 'png',
        'image/webp'       => 'webp',
        'application/msword' => 'doc',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
    ];
    private const MAX_BYTES = 10 * 1024 * 1024;

    private EmployeeDocumentModel $documents;

    public function __construct(private AuditService $audit = new AuditService())
    {
        $this->documents = new EmployeeDocumentModel(service('tenantContext')->db());
    }

    public function upload(
        int $employeeId,
        UploadedFile $file,
        string $documentType,
        ?string $documentNumber,
        ?string $expiryDate,
        ?string $remarks
    ): int {
        if (! $file->isValid() || $file->hasMoved()) {
            throw new RuntimeException('Upload failed — please try again.');
        }

        if ($file->getSize() > self::MAX_BYTES) {
            throw new RuntimeException('Document must be 10MB or smaller.');
        }

        $realMime = (new \finfo(FILEINFO_MIME_TYPE))->file($file->getTempName());
        if (! isset(self::ALLOWED_MIME[$realMime])) {
            throw new RuntimeException('Unsupported file type. Allowed: PDF, JPG, PNG, WEBP, DOC, DOCX.');
        }

        $ext = self::ALLOWED_MIME[$realMime];
        $dir = WRITEPATH . 'uploads/tenants/' . tenant()->companyCode() . '/employees/' . $employeeId . '/documents';
        if (! is_dir($dir) && ! mkdir($dir, 0750, true) && ! is_dir($dir)) {
            throw new RuntimeException('Could not create the upload directory.');
        }

        $storedName = bin2hex(random_bytes(16)) . '.' . $ext;
        $file->move($dir, $storedName);

        $relativePath = 'tenants/' . tenant()->companyCode() . '/employees/' . $employeeId . '/documents/' . $storedName;

        $id = $this->documents->insert([
            'employee_id'       => $employeeId,
            'document_type'     => $documentType,
            'document_number'   => $documentNumber ?: null,
            'file_path'         => $relativePath,
            'original_filename' => $file->getClientName(),
            'mime_type'         => $realMime,
            'file_size'         => $file->getSize(),
            'expiry_date'       => $expiryDate ?: null,
            'status'            => 'pending',
            'remarks'           => $remarks ?: null,
            'uploaded_by'       => session('tenant_user_id'),
        ]);

        $this->audit->log('document_upload', 'employees', 'employee_document', $id, null, [
            'employee_id' => $employeeId, 'document_type' => $documentType, 'original_filename' => $file->getClientName(),
        ]);

        return $id;
    }

    /** Soft delete only — the file stays on disk in case the row needs to be recovered/audited. */
    public function delete(int $documentId): void
    {
        $doc = $this->documents->find($documentId);
        if (! $doc) {
            throw new RuntimeException('Document not found.');
        }

        $this->documents->delete($documentId);
        $this->audit->log('document_delete', 'employees', 'employee_document', $documentId, $doc, null);
    }
}
