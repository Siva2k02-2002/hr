<?php

namespace App\Services;

use App\Models\CompanySettingModel;
use App\Models\RoleModel;
use RuntimeException;

class RoleService
{
    private RoleModel $roles;

    public function __construct(private AuditService $audit = new AuditService())
    {
        $this->roles = new RoleModel(service('tenantContext')->db());
    }

    public function create(array $data): int
    {
        $id = $this->roles->insert([
            'name'        => $data['name'],
            'slug'        => $this->uniqueSlug($data['name']),
            'description' => $data['description'] ?: null,
            'is_system'   => 0,
            'status'      => 'active',
        ]);
        $this->audit->log('create', 'roles', 'role', $id, null, ['name' => $data['name']]);

        return $id;
    }

    public function update(int $id, array $data): void
    {
        $role = $this->roles->find($id);
        if (! $role) {
            throw new RuntimeException('Role not found.');
        }

        // System roles keep their name/slug fixed — only the description may change.
        $update = ['description' => $data['description'] ?: null];
        if (! $role['is_system']) {
            $update['name'] = $data['name'];
        }

        $this->roles->update($id, $update);
        $this->audit->log('update', 'roles', 'role', $id, $role, $update);
    }

    public function duplicate(int $id): int
    {
        $role = $this->roles->find($id);
        if (! $role) {
            throw new RuntimeException('Role not found.');
        }

        $newId = $this->roles->insert([
            'name'        => $role['name'] . ' (Copy)',
            'slug'        => $this->slugify($role['name'] . '-copy-' . bin2hex(random_bytes(3))),
            'description' => $role['description'],
            'is_system'   => 0,
            'status'      => 'active',
        ]);

        $this->roles->syncPermissions($newId, $this->roles->permissionIds($id));
        $this->audit->log('duplicate', 'roles', 'role', $newId, null, ['from_role_id' => $id]);

        return $newId;
    }

    public function archive(int $id): void
    {
        $role = $this->roles->find($id);
        if (! $role) {
            throw new RuntimeException('Role not found.');
        }
        if ($role['is_system']) {
            throw new RuntimeException('System roles cannot be archived.');
        }

        $this->roles->update($id, ['status' => 'archived']);
        $this->audit->log('archive', 'roles', 'role', $id, ['status' => $role['status']], ['status' => 'archived']);
    }

    public function restore(int $id): void
    {
        $role = $this->roles->find($id);
        if (! $role) {
            throw new RuntimeException('Role not found.');
        }

        $this->roles->update($id, ['status' => 'active']);
        $this->audit->log('restore', 'roles', 'role', $id, ['status' => $role['status']], ['status' => 'active']);
    }

    public function syncPermissions(int $id, array $permissionIds): void
    {
        $role = $this->roles->find($id);
        if (! $role) {
            throw new RuntimeException('Role not found.');
        }

        $before = $this->roles->permissionIds($id);
        $this->roles->syncPermissions($id, $permissionIds);
        (new CompanySettingModel(service('tenantContext')->db()))->bumpPermissionsVersion();

        $this->audit->log('permissions_assigned', 'roles', 'role', $id, ['permission_ids' => $before], ['permission_ids' => $permissionIds]);
    }

    private function slugify(string $name): string
    {
        $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $name), '-'));

        return $slug !== '' ? $slug : 'role-' . bin2hex(random_bytes(3));
    }

    /** RoleModel can't use is_unique against a dynamic tenant connection — see UserModel's note. */
    private function uniqueSlug(string $name): string
    {
        $base = $this->slugify($name);
        $slug = $base;
        $n    = 1;
        while ($this->roles->where('slug', $slug)->first()) {
            $slug = $base . '-' . ++$n;
        }

        return $slug;
    }
}
