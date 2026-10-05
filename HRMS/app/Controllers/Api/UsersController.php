<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\UserModel;

/**
 * AJAX data source for the Manager / Head-of-Department searchable
 * selects. Route is permission-filtered exactly like any other route
 * (see Routes.php: 'permission:users.view') — TenantPermissionFilter
 * returns JSON 403 here instead of the HTML 403 page because this
 * controller only ever serves JSON.
 */
class UsersController extends BaseController
{
    public function search()
    {
        if ($this->rateLimited('api_users_search', 60, MINUTE)) {
            return $this->response->setStatusCode(429)->setJSON(['status' => 'failed', 'message' => 'Too many requests.']);
        }

        $q     = trim((string) $this->request->getGet('q'));
        $model = (new UserModel(service('tenantContext')->db()))->where('status', 'active');

        if ($q !== '') {
            $model->groupStart()->like('name', $q)->orLike('email', $q)->groupEnd();
        }

        $users = $model->orderBy('name')->findAll(20);

        return $this->response->setJSON([
            'results' => array_map(static fn ($u) => ['id' => $u['id'], 'text' => $u['name'] . ' (' . $u['email'] . ')'], $users),
        ]);
    }
}
