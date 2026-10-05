<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\EmployeeModel;

/**
 * AJAX data source for the Reporting Manager select2 field (employees/form.php)
 * and the Manager filter (employees/index.php). Same shape as Api\UsersController::search.
 */
class EmployeesController extends BaseController
{
    public function search()
    {
        if ($this->rateLimited('api_employees_search', 60, MINUTE)) {
            return $this->response->setStatusCode(429)->setJSON(['status' => 'failed', 'message' => 'Too many requests.']);
        }

        $q            = trim((string) $this->request->getGet('q'));
        $excludeId    = $this->request->getGet('exclude');
        $model        = (new EmployeeModel(service('tenantContext')->db()))->where('status !=', 'terminated');

        if ($excludeId) {
            $model->where('id !=', (int) $excludeId);
        }

        // Users module's employee-link picker (users/form.php): only offer employees
        // that don't already have a login account.
        if ($this->request->getGet('unlinked')) {
            $model->where('user_id', null);
        }

        if ($q !== '') {
            $model->groupStart()
                ->like('employee_code', $q)
                ->orLike('first_name', $q)
                ->orLike('last_name', $q)
                ->groupEnd();
        }

        $employees = $model->orderBy('first_name')->findAll(20);

        return $this->response->setJSON([
            'results' => array_map(static fn ($e) => [
                'id'   => $e['id'],
                'text' => trim($e['first_name'] . ' ' . $e['last_name']) . ' (' . $e['employee_code'] . ')',
            ], $employees),
        ]);
    }
}
