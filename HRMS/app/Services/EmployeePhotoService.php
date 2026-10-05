<?php

namespace App\Services;

use App\Models\EmployeeModel;
use CodeIgniter\HTTP\Files\UploadedFile;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use RuntimeException;

/**
 * Every photo is normalized to a square JPEG regardless of the source
 * format (JPG/PNG/WEBP accepted) — keeps storage/serving simple and
 * guarantees the "crop square, resize, compress" requirement is met the
 * same way every time. Stored outside the public webroot; only ever
 * reachable through EmployeePhotoController's permission-checked stream.
 */
class EmployeePhotoService
{
    private const ALLOWED_MIME = ['image/jpeg', 'image/png', 'image/webp'];
    private const MAX_BYTES    = 5 * 1024 * 1024;
    private const DIMENSION    = 500;

    private EmployeeModel $employees;

    public function __construct(private AuditService $audit = new AuditService())
    {
        $this->employees = new EmployeeModel(service('tenantContext')->db());
    }

    public function upload(int $employeeId, UploadedFile $file): string
    {
        $employee = $this->employees->find($employeeId);
        if (! $employee) {
            throw new RuntimeException('Employee not found.');
        }

        if (! $file->isValid() || $file->hasMoved()) {
            throw new RuntimeException('Upload failed — please try again.');
        }

        if ($file->getSize() > self::MAX_BYTES) {
            throw new RuntimeException('Photo must be 5MB or smaller.');
        }

        // Never trust the client-supplied extension/MIME header — sniff the real bytes.
        $realMime = (new \finfo(FILEINFO_MIME_TYPE))->file($file->getTempName());
        if (! in_array($realMime, self::ALLOWED_MIME, true)) {
            throw new RuntimeException('Photo must be a JPG, PNG, or WEBP image.');
        }

        $dir = $this->directoryFor($employeeId);
        if (! is_dir($dir) && ! mkdir($dir, 0750, true) && ! is_dir($dir)) {
            throw new RuntimeException('Could not create the upload directory.');
        }

        $manager = new ImageManager(Driver::class);
        $image   = $manager->read($file->getTempName());
        $image->cover(self::DIMENSION, self::DIMENSION);

        $relativePath = 'tenants/' . tenant()->companyCode() . '/employees/' . $employeeId . '/photo.jpg';
        $absolutePath = WRITEPATH . 'uploads/' . $relativePath;

        // Fixed filename per employee — a re-upload simply overwrites it, which is
        // the "delete old file on replace" requirement without needing a separate step.
        $image->toJpeg(80)->save($absolutePath);

        $this->employees->update($employeeId, [
            'photo_path' => $relativePath,
            'updated_by' => session('tenant_user_id'),
        ]);

        $this->audit->log('photo_update', 'employees', 'employee', $employeeId,
            ['photo_path' => $employee['photo_path']], ['photo_path' => $relativePath]);

        return $relativePath;
    }

    public function absolutePathFor(array $employee): ?string
    {
        if (empty($employee['photo_path'])) {
            return null;
        }

        $path = WRITEPATH . 'uploads/' . $employee['photo_path'];

        return is_file($path) ? $path : null;
    }

    private function directoryFor(int $employeeId): string
    {
        return WRITEPATH . 'uploads/tenants/' . tenant()->companyCode() . '/employees/' . $employeeId;
    }
}
