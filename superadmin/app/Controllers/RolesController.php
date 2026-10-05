<?php

namespace App\Controllers;

use App\Models\PlatformPermissionModel;
use App\Models\PlatformRoleModel;
use App\Services\AuditService;
use CodeIgniter\Exceptions\PageNotFoundException;

class RolesController extends BaseController
{
    public function index()
    {
        $roleModel = new PlatformRoleModel();
        $roles = $roleModel->orderBy('name')->findAll();

        foreach ($roles as &$role) {
            $role['permission_count'] = count($roleModel->permissionSlugs((int) $role['id']));
        }
        unset($role);

        return view('roles/index', ['title' => 'Roles', 'roles' => $roles]);
    }

    public function create()
    {
        return view('roles/form', [
            'title' => 'Add role',
            'role'  => null,
            'permissionGroups' => (new PlatformPermissionModel())->groupedByModule(),
            'rolePermissionSlugs' => [],
        ]);
    }

    public function store()
    {
        $roleModel = new PlatformRoleModel();
        $rules = ['name' => 'required|min_length[2]|max_length[100]', 'slug' => 'required|alpha_dash|is_unique[platform_roles.slug]'];

        if (! $this->validate($rules)) {
            return view('roles/form', [
                'title' => 'Add role', 'role' => null,
                'permissionGroups' => (new PlatformPermissionModel())->groupedByModule(),
                'rolePermissionSlugs' => [],
                'errors' => $this->validator->getErrors(),
            ]);
        }

        $roleId = $roleModel->insert([
            'name'      => $this->request->getPost('name'),
            'slug'      => strtolower($this->request->getPost('slug')),
            'is_system' => 0,
        ]);

        $permissionIds = array_map('intval', (array) $this->request->getPost('permissions'));
        $roleModel->syncPermissions($roleId, $permissionIds);

        (new AuditService())->log('create', 'role', 'role', $roleId, null, ['name' => $this->request->getPost('name'), 'permissions' => $permissionIds]);

        return redirect()->to(site_url('roles'))->with('success', 'Role created.');
    }

    public function edit($id)
    {
        $roleModel = new PlatformRoleModel();
        $role = $roleModel->find($id);
        if (! $role) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('roles/form', [
            'title' => 'Edit role',
            'role'  => $role,
            'permissionGroups' => (new PlatformPermissionModel())->groupedByModule(),
            'rolePermissionSlugs' => $roleModel->permissionSlugs((int) $id),
        ]);
    }

    public function update($id)
    {
        $roleModel = new PlatformRoleModel();
        $role = $roleModel->find($id);
        if (! $role) {
            throw PageNotFoundException::forPageNotFound();
        }

        if (! $role['is_system']) {
            $rules = ['name' => 'required|min_length[2]|max_length[100]'];
            if (! $this->validate($rules)) {
                return view('roles/form', [
                    'title' => 'Edit role', 'role' => $role,
                    'permissionGroups' => (new PlatformPermissionModel())->groupedByModule(),
                    'rolePermissionSlugs' => $roleModel->permissionSlugs((int) $id),
                    'errors' => $this->validator->getErrors(),
                ]);
            }

            $roleModel->update($id, ['name' => $this->request->getPost('name')]);
        }

        $permissionIds = array_map('intval', (array) $this->request->getPost('permissions'));
        $roleModel->syncPermissions((int) $id, $permissionIds);

        (new AuditService())->log('update', 'role', 'role', (int) $id, $role, ['name' => $this->request->getPost('name'), 'permissions' => $permissionIds]);

        return redirect()->to(site_url('roles'))->with('success', 'Role updated.');
    }
}
