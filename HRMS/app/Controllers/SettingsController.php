<?php

namespace App\Controllers;

use App\Models\CompanySettingModel;
use App\Services\AuditService;

class SettingsController extends BaseController
{
    public function index()
    {
        return view('settings/index', ['title' => 'Company Settings', 'settings' => (new CompanySettingModel(service('tenantContext')->db()))->current()]);
    }

    public function update()
    {
        $rules = ['company_name' => 'required|min_length[2]|max_length[150]'];
        if (! $this->validate($rules)) {
            return redirect()->back()->with('error', 'Please check the form and try again.');
        }

        $model = new CompanySettingModel(service('tenantContext')->db());
        $old   = $model->current();
        $new   = [
            'company_name' => $this->request->getPost('company_name'),
            'timezone'     => $this->request->getPost('timezone'),
            'currency'     => $this->request->getPost('currency'),
            'date_format'  => $this->request->getPost('date_format'),
            'time_format'  => $this->request->getPost('time_format'),
            'require_email_verification' => $this->request->getPost('require_email_verification') ? 1 : 0,
            'theme'            => in_array($this->request->getPost('theme'), ['light', 'dark', 'auto'], true) ? $this->request->getPost('theme') : 'light',
            'font_family'      => $this->request->getPost('font_family') ?: 'system',
            'default_language' => $this->request->getPost('default_language') ?: 'en',
            'week_start_day'   => in_array((int) $this->request->getPost('week_start_day'), [0, 1], true) ? (int) $this->request->getPost('week_start_day') : 1,
        ];
        $model->update($old['id'], $new);
        (new AuditService())->logUpdate('settings', 'company_setting', (int) $old['id'], $old, $new);
        (new \App\Services\LookupCacheService())->invalidate('company_settings');

        return redirect()->to(site_url('settings'))->with('success', 'Settings updated.');
    }
}
