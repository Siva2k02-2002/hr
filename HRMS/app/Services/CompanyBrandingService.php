<?php

namespace App\Services;

use App\Models\CompanySettingModel;
use CodeIgniter\HTTP\Files\UploadedFile;
use RuntimeException;

/** Same MIME-sniffing/random-filename/outside-webroot pattern as EmployeePhotoService — see that class for the rationale. */
class CompanyBrandingService
{
    private const ALLOWED_MIME = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/x-icon' => 'ico', 'image/vnd.microsoft.icon' => 'ico'];
    private const MAX_BYTES    = 2 * 1024 * 1024;

    public function __construct(
        private AuditService $audit = new AuditService(),
        private LookupCacheService $cache = new LookupCacheService()
    ) {
    }

    /** @param 'logo'|'favicon'|'banner' $kind */
    public function upload(string $kind, UploadedFile $file): string
    {
        if (! $file->isValid() || $file->hasMoved()) {
            throw new RuntimeException('Upload failed — please try again.');
        }
        if ($file->getSize() > self::MAX_BYTES) {
            throw new RuntimeException('Image must be 2MB or smaller.');
        }

        $realMime = (new \finfo(FILEINFO_MIME_TYPE))->file($file->getTempName());
        if (! isset(self::ALLOWED_MIME[$realMime])) {
            throw new RuntimeException('Unsupported file type. Allowed: JPG, PNG, WEBP' . ($kind === 'favicon' ? ', ICO' : '') . '.');
        }

        $dir = WRITEPATH . 'uploads/tenants/' . tenant()->companyCode() . '/branding';
        if (! is_dir($dir) && ! mkdir($dir, 0750, true) && ! is_dir($dir)) {
            throw new RuntimeException('Could not create the upload directory.');
        }

        $relativePath = 'tenants/' . tenant()->companyCode() . '/branding/' . $kind . '.' . self::ALLOWED_MIME[$realMime];
        $file->move($dir, $kind . '.' . self::ALLOWED_MIME[$realMime]);

        $model  = new CompanySettingModel(service('tenantContext')->db());
        $old    = $model->current();
        $field  = $kind . '_path';
        $model->update($old['id'], [$field => $relativePath]);

        $this->audit->logUpdate('settings', 'company_setting', (int) $old['id'], [$field => $old[$field] ?? null], [$field => $relativePath]);
        $this->cache->invalidate('company_settings');

        return $relativePath;
    }
}
