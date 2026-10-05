<?php

namespace App\Controllers;

use App\Models\PermissionModel;
use App\Models\RoleModel;
use App\Services\RoleService;
use CodeIgniter\Exceptions\PageNotFoundException;
use Config\TenantPermissions;
use RuntimeException;

class RolesController extends BaseController
{
    public function index()
    {
        $status = (string) $this->request->getGet('status') ?: 'active';
        $model  = new RoleModel(service('tenantContext')->db());

        if ($status !== '') {
            $model->where('status', $status);
        }

        $roles = $model->orderBy('is_system', 'DESC')->orderBy('name', 'ASC')->findAll();
        foreach ($roles as &$role) {
            $role['user_count'] = $model->userCount((int) $role['id']);
        }

        return view('roles/index', ['title' => 'Roles', 'roles' => $roles, 'filters' => ['status' => $status]]);
    }

    public function create()
    {
        return view('roles/form', ['title' => 'Add role', 'role' => null]);
    }

    public function store()
    {
        if (! $this->validate(['name' => 'required|min_length[2]|max_length[100]'])) {
            return view('roles/form', ['title' => 'Add role', 'role' => null, 'errors' => $this->validator->getErrors()]);
        }

        $id = (new RoleService())->create($this->request->getPost());

        return redirect()->to(site_url('roles/' . $id . '/permissions'))->with('success', 'Role created. Now set its permissions.');
    }

    public function edit($id)
    {
        $role = (new RoleModel(service('tenantContext')->db()))->find($id);
        if (! $role) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('roles/form', ['title' => 'Edit role', 'role' => $role]);
    }

    public function update($id)
    {
        $role = (new RoleModel(service('tenantContext')->db()))->find($id);
        if (! $role) {
            throw PageNotFoundException::forPageNotFound();
        }

        if (! $this->validate(['name' => 'required|min_length[2]|max_length[100]'])) {
            return view('roles/form', ['title' => 'Edit role', 'role' => $role, 'errors' => $this->validator->getErrors()]);
        }

        (new RoleService())->update((int) $id, $this->request->getPost());

        return redirect()->to(site_url('roles'))->with('success', 'Role updated.');
    }

    public function duplicate($id)
    {
        $newId = (new RoleService())->duplicate((int) $id);

        return redirect()->to(site_url('roles/' . $newId . '/permissions'))->with('success', 'Role duplicated.');
    }

    public function archive($id)
    {
        try {
            (new RoleService())->archive((int) $id);
        } catch (RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('roles'))->with('success', 'Role archived.');
    }

    public function restore($id)
    {
        (new RoleService())->restore((int) $id);

        return redirect()->to(site_url('roles'))->with('success', 'Role restored.');
    }

    /** The Role -> Permission matrix — modules as rows, actions as columns. */
    public function permissions($id)
    {
        $role = (new RoleModel(service('tenantContext')->db()))->find($id);
        if (! $role) {
            throw PageNotFoundException::forPageNotFound();
        }

        $granted = (new RoleModel(service('tenantContext')->db()))->permissionSlugs((int) $id);
        $permissionRows = (new PermissionModel(service('tenantContext')->db()))->orderBy('module')->orderBy('slug')->findAll();
        $permissionIdBySlug = array_column($permissionRows, 'id', 'slug');

        return view('roles/matrix', [
            'title'       => 'Permissions — ' . $role['name'],
            'role'        => $role,
            'catalog'     => TenantPermissions::catalog(),
            'granted'     => $granted,
            'idBySlug'    => $permissionIdBySlug,
        ]);
    }

    public function savePermissions($id)
    {
        $role = (new RoleModel(service('tenantContext')->db()))->find($id);
        if (! $role) {
            throw PageNotFoundException::forPageNotFound();
        }
        if ($role['is_system'] && $role['slug'] === 'company-admin') {
            return redirect()->back()->with('error', 'The Company Admin role always has every permission and cannot be changed.');
        }
        if ($role['is_system'] && $role['slug'] === 'employee') {
            return redirect()->back()->with('error', 'The Employee role has a fixed, minimal self-service permission set and cannot be changed here. Duplicate this role if you need a customizable variant.');
        }

        $permissionIds = array_map('intval', $this->request->getPost('permission_ids') ?? []);
        (new RoleService())->syncPermissions((int) $id, $permissionIds);

        return redirect()->to(site_url('roles/' . $id . '/permissions'))->with('success', 'Permissions updated.');
    }
}
