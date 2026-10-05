<?php

namespace App\Controllers;

use App\Models\AuditLogModel;

class AuditLogsController extends BaseController
{
    public function index()
    {
        $model  = (new AuditLogModel(service('tenantContext')->db()))->withUserName();
        $module = (string) $this->request->getGet('module');
        $search = trim((string) $this->request->getGet('q'));

        if ($module !== '') {
            $model->where('audit_logs.module', $module);
        }
        if ($search !== '') {
            $model->groupStart()->like('audit_logs.action', $search)->orLike('u.name', $search)->groupEnd();
        }

        $logs = $model->orderBy('audit_logs.id', 'DESC')->paginate(20, 'logs');

        return view('audit_logs/index', [
            'title'   => 'Audit Logs',
            'logs'    => $logs,
            'pager'   => $model->pager,
            'modules' => ['users', 'roles', 'branches', 'departments', 'designations'],
            'filters' => ['module' => $module, 'q' => $search],
        ]);
    }
}
