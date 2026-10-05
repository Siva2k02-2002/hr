<?php

namespace App\Controllers;

use App\Models\PlatformRoleModel;
use App\Models\PlatformUserModel;
use App\Services\AuditService;
use CodeIgniter\Exceptions\PageNotFoundException;
use RuntimeException;

class PlatformUsersController extends BaseController
{
    public function index()
    {
        $userModel = new PlatformUserModel();
        $users = $userModel->orderBy('name')->findAll();

        foreach ($users as &$user) {
            $user['role_names'] = implode(', ', $userModel->roleSlugs((int) $user['id']));
        }
        unset($user);

        return view('platform_users/index', ['title' => 'Platform Users', 'users' => $users]);
    }

    public function create()
    {
        return view('platform_users/form', [
            'title' => 'Add platform user',
            'user'  => null,
            'roles' => (new PlatformRoleModel())->findAll(),
            'userRoleIds' => [],
        ]);
    }

    public function store()
    {
        $rules = [
            'name'     => 'required|min_length[2]|max_length[150]',
            'email'    => 'required|valid_email|is_unique[platform_users.email]',
            'password' => 'required|min_length[8]',
        ];

        if (! $this->validate($rules)) {
            return view('platform_users/form', [
                'title' => 'Add platform user', 'user' => null,
                'roles' => (new PlatformRoleModel())->findAll(), 'userRoleIds' => [],
                'errors' => $this->validator->getErrors(),
            ]);
        }

        $userModel = new PlatformUserModel();
        $userId = $userModel->insert([
            'name'                 => $this->request->getPost('name'),
            'email'                => $this->request->getPost('email'),
            'password_hash'        => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'status'                => 'active',
            'must_change_password' => 1,
        ]);

        $roleIds = array_map('intval', (array) $this->request->getPost('roles'));

        try {
            $this->assertCanAssignRoles($roleIds);
        } catch (RuntimeException $e) {
            $userModel->delete($userId);

            return view('platform_users/form', [
                'title' => 'Add platform user', 'user' => null,
                'roles' => (new PlatformRoleModel())->findAll(), 'userRoleIds' => [],
                'errors' => ['form' => $e->getMessage()],
            ]);
        }

        $this->syncUserRoles($userId, $roleIds);

        (new AuditService())->log('create', 'platform_user', 'platform_user', $userId, null, ['name' => $this->request->getPost('name'), 'email' => $this->request->getPost('email'), 'roles' => $roleIds]);

        return redirect()->to(site_url('platform-users'))->with('success', 'Platform user created.');
    }

    public function edit($id)
    {
        $userModel = new PlatformUserModel();
        $user = $userModel->find($id);
        if (! $user) {
            throw PageNotFoundException::forPageNotFound();
        }

        $roleIds = array_column(
            $userModel->db->table('platform_user_roles')->select('role_id')->where('user_id', $id)->get()->getResultArray(),
            'role_id'
        );

        return view('platform_users/form', [
            'title' => 'Edit platform user',
            'user'  => $user,
            'roles' => (new PlatformRoleModel())->findAll(),
            'userRoleIds' => array_map('intval', $roleIds),
        ]);
    }

    public function update($id)
    {
        $userModel = new PlatformUserModel();
        $user = $userModel->find($id);
        if (! $user) {
            throw PageNotFoundException::forPageNotFound();
        }

        $rules = ['name' => 'required|min_length[2]|max_length[150]'];
        if (! $this->validate($rules)) {
            return view('platform_users/form', [
                'title' => 'Edit platform user', 'user' => $user,
                'roles' => (new PlatformRoleModel())->findAll(),
                'userRoleIds' => array_map('intval', array_column(
                    $userModel->db->table('platform_user_roles')->select('role_id')->where('user_id', $id)->get()->getResultArray(),
                    'role_id'
                )),
                'errors' => $this->validator->getErrors(),
            ]);
        }

        $data = ['name' => $this->request->getPost('name')];

        $newPassword = $this->request->getPost('password');
        if ($newPassword !== null && $newPassword !== '') {
            $data['password_hash']        = password_hash($newPassword, PASSWORD_DEFAULT);
            $data['must_change_password'] = 1;
        }

        $roleIds = array_map('intval', (array) $this->request->getPost('roles'));

        try {
            $this->assertCanAssignRoles($roleIds);
        } catch (RuntimeException $e) {
            return view('platform_users/form', [
                'title' => 'Edit platform user', 'user' => $user,
                'roles' => (new PlatformRoleModel())->findAll(),
                'userRoleIds' => array_map('intval', array_column(
                    $userModel->db->table('platform_user_roles')->select('role_id')->where('user_id', $id)->get()->getResultArray(),
                    'role_id'
                )),
                'errors' => ['form' => $e->getMessage()],
            ]);
        }

        $userModel->update($id, $data);
        $this->syncUserRoles((int) $id, $roleIds);

        (new AuditService())->log('update', 'platform_user', 'platform_user', (int) $id, $user, ['name' => $data['name'], 'roles' => $roleIds]);

        return redirect()->to(site_url('platform-users'))->with('success', 'Platform user updated.');
    }

    public function toggle($id)
    {
        $userModel = new PlatformUserModel();
        $user = $userModel->find($id);
        if (! $user) {
            throw PageNotFoundException::forPageNotFound();
        }

        if ((int) $id === $this->currentUserId()) {
            return redirect()->back()->with('error', 'You cannot deactivate your own account.');
        }

        $newStatus = $user['status'] === 'active' ? 'inactive' : 'active';
        $userModel->update($id, ['status' => $newStatus]);

        (new AuditService())->log('status_change', 'platform_user', 'platform_user', (int) $id, ['status' => $user['status']], ['status' => $newStatus]);

        return redirect()->back()->with('success', 'User ' . ($newStatus === 'active' ? 'activated' : 'deactivated') . '.');
    }

    /**
     * `user.manage` is what gates this whole controller, and nothing stops a future custom
     * platform role from holding it without also holding super-admin — so the role picker
     * alone can't be trusted to keep a lesser-privileged actor from POSTing the super-admin
     * role id straight past it. Only an actor who already holds super-admin may grant it.
     */
    private function assertCanAssignRoles(array $roleIds): void
    {
        if ($roleIds === []) {
            return;
        }

        $targetSlugs = array_column((new PlatformRoleModel())->whereIn('id', $roleIds)->findAll(), 'slug');
        if (! in_array('super-admin', $targetSlugs, true)) {
            return;
        }

        $actorId = (int) $this->currentUserId();
        if (! in_array('super-admin', (new PlatformUserModel())->roleSlugs($actorId), true)) {
            throw new RuntimeException('Only a Super Admin can grant the Super Admin role.');
        }
    }

    private function syncUserRoles(int $userId, array $roleIds): void
    {
        $db = db_connect();
        $db->table('platform_user_roles')->where('user_id', $userId)->delete();

        if ($roleIds === []) {
            return;
        }

        $rows = array_map(static fn ($roleId) => [
            'user_id'    => $userId,
            'role_id'    => $roleId,
            'created_at' => date('Y-m-d H:i:s'),
        ], $roleIds);

        $db->table('platform_user_roles')->insertBatch($rows);
    }
}
