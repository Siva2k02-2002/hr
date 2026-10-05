<?php

namespace App\Controllers;

use App\Models\PlatformPermissionModel;

class PermissionsController extends BaseController
{
    /**
     * Read-only by design — permissions are seeded from
     * Config\PlatformPermissions, the single source of truth, so there is
     * no create/edit/delete UI here to avoid the table drifting from code.
     */
    public function index()
    {
        return view('permissions/index', [
            'title'  => 'Permissions',
            'groups' => (new PlatformPermissionModel())->groupedByModule(),
        ]);
    }
}
