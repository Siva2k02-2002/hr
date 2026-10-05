<?php

namespace App\Services;

use Config\TenantMenu;

/**
 * Filters Config\TenantMenu against the current user's permissions. A
 * top-level item with no `permission` key (e.g. a group label) is kept
 * only if at least one of its children survives filtering — this is the
 * actual "menu permission engine": nothing about visibility is decided
 * in the view.
 */
class MenuBuilder
{
    public function __construct(private PermissionService $permissions = new PermissionService())
    {
    }

    public function build(): array
    {
        return $this->filter(TenantMenu::tree());
    }

    private function filter(array $items): array
    {
        $visible = [];

        foreach ($items as $item) {
            if (isset($item['children'])) {
                $children = $this->filter($item['children']);
                if ($children !== []) {
                    $item['children'] = $children;
                    $visible[] = $item;
                }

                continue;
            }

            if (! isset($item['permission']) || $this->permissions->can($item['permission'])) {
                $visible[] = $item;
            }
        }

        return $visible;
    }
}
