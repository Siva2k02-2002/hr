<?php

namespace App\Controllers;

use App\Models\PermissionModel;

/** Read-only — permissions are code-defined (Config\TenantPermissions), never hand-edited in the DB. */
class PermissionsController extends BaseController
{
    public function index()
    {
        $permissions = (new PermissionModel(service('tenantContext')->db()))->orderBy('module')->orderBy('slug')->findAll();
        $byModule    = [];
        foreach ($permissions as $p) {
            $byModule[$p['module']][] = $p;
        }

        return view('permissions/index', ['title' => 'Permissions', 'byModule' => $byModule]);
    }
}
