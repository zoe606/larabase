<?php

declare(strict_types=1);

namespace App\Actions\Permission;

use App\Contracts\ActionInterface;
use App\Queries\Permission\GetDistinctGroupsQuery;
use App\Queries\Permission\GetPermissionGroupsQuery;
use Spatie\Permission\Models\Permission;

final class CreatePermission implements ActionInterface
{
    /**
     * Create a new permission.
     *
     * @param  array{name: string, group?: string|null}  $data
     */
    public function handle(array $data): Permission
    {
        $permission = Permission::create([
            'name' => $data['name'],
            'group' => $data['group'] ?? null,
            'guard_name' => 'web',
        ]);

        // Clear permission caches
        GetPermissionGroupsQuery::clearCache();
        GetDistinctGroupsQuery::clearCache();

        return $permission;
    }
}
