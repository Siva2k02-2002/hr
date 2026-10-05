<?php

namespace App\Controllers;

use App\Models\PlatformSettingModel;
use App\Services\AuditService;

class SettingsController extends BaseController
{
    private const KEYS = ['platform_name', 'support_email', 'default_timezone', 'default_currency'];

    public function index()
    {
        $model = new PlatformSettingModel();
        $settings = [];
        foreach (self::KEYS as $key) {
            $settings[$key] = $model->get($key, $this->defaultFor($key));
        }

        return view('settings/index', ['title' => 'Settings', 'settings' => $settings]);
    }

    public function update()
    {
        $model = new PlatformSettingModel();
        $old = [];
        $new = [];

        foreach (self::KEYS as $key) {
            $old[$key] = $model->get($key, $this->defaultFor($key));
            $value = (string) $this->request->getPost($key);
            $new[$key] = $value;
            $model->setValue($key, $value);
        }

        (new AuditService())->log('update', 'settings', 'platform_settings', null, $old, $new);

        return redirect()->to(site_url('settings'))->with('success', 'Settings updated.');
    }

    private function defaultFor(string $key): string
    {
        return match ($key) {
            'platform_name'    => 'HRMS Platform',
            'support_email'    => 'support@example.com',
            'default_timezone' => 'Asia/Kolkata',
            'default_currency' => 'INR',
            default            => '',
        };
    }
}
