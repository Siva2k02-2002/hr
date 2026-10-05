<?php

namespace App\Controllers;

use App\Models\CompanySettingModel;
use App\Services\CompanyBrandingService;
use CodeIgniter\Exceptions\PageNotFoundException;
use RuntimeException;

class CompanyBrandingController extends BaseController
{
    private const KINDS = ['logo', 'favicon', 'banner'];
    private const MIME_BY_EXT = ['jpg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp', 'ico' => 'image/x-icon'];

    public function upload($kind)
    {
        if (! in_array($kind, self::KINDS, true)) {
            throw PageNotFoundException::forPageNotFound();
        }

        $file = $this->request->getFile($kind);
        if (! $file) {
            return redirect()->back()->with('error', 'Please choose an image to upload.');
        }

        try {
            (new CompanyBrandingService())->upload($kind, $file);
        } catch (RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('settings'))->with('success', ucfirst($kind) . ' updated.');
    }

    /**
     * Deliberately reachable without the `auth` filter (only `tenant`) — the login
     * page itself shows the company's logo/favicon, same as it already shows
     * company_name() to a signed-out visitor.
     */
    public function show($kind)
    {
        if (! in_array($kind, self::KINDS, true)) {
            throw PageNotFoundException::forPageNotFound();
        }

        $settings = (new CompanySettingModel(service('tenantContext')->db()))->current();
        $relativePath = $settings[$kind . '_path'] ?? null;
        if (! $relativePath) {
            throw PageNotFoundException::forPageNotFound();
        }

        $absolutePath = WRITEPATH . 'uploads/' . $relativePath;
        if (! is_file($absolutePath)) {
            throw PageNotFoundException::forPageNotFound();
        }

        $ext = strtolower((string) pathinfo($absolutePath, PATHINFO_EXTENSION));

        return $this->response
            ->setHeader('Content-Type', self::MIME_BY_EXT[$ext] ?? 'application/octet-stream')
            ->setHeader('Cache-Control', 'public, max-age=3600')
            ->setBody(file_get_contents($absolutePath));
    }
}
