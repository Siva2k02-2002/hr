<?php

namespace App\Controllers;

use App\Models\AuditLogModel;
use App\Models\LoginLogModel;

class AuditLogsController extends BaseController
{
    public function index()
    {
        $model = (new AuditLogModel())->withActor();

        $module = (string) $this->request->getGet('module');
        if ($module !== '') {
            $model->where('audit_logs.module', $module);
        }

        $logs = $model->orderBy('audit_logs.created_at', 'DESC')->paginate(25, 'audit');

        return view('audit_logs/index', [
            'title'   => 'Audit Logs',
            'logs'    => $logs,
            'pager'   => $model->pager,
            'module'  => $module,
            'modules' => ['company', 'plan', 'module', 'subscription', 'platform_user', 'role'],
        ]);
    }

    public function logins()
    {
        $model = new LoginLogModel();
        $logs  = $model->orderBy('created_at', 'DESC')->paginate(25, 'logins');

        return view('audit_logs/logins', [
            'title' => 'Login Logs',
            'logs'  => $logs,
            'pager' => $model->pager,
        ]);
    }
}
