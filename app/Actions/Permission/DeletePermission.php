<?php

declare(strict_types=1);

namespace App\Actions\Permission;

use App\Contracts\ActionInterface;
use App\Queries\Permission\GetDistinctGroupsQuery;
use App\Queries\Permission\GetPermissionGroupsQuery;
use Spatie\Permission\Models\Permission;

final class DeletePermission implements ActionInterface
{
    /**
     * Delete a permission.
     *
     * @param  array{id: int}  $data
     */
    public function handle(array $data): bool
    {
        $permission = Permission::findOrFail($data['id']);

        $result = (bool) $permission->delete();

        // Clear permission caches
        GetPermissionGroupsQuery::clearCache();
        GetDistinctGroupsQuery::clearCache();

        return $result;
    }
}
