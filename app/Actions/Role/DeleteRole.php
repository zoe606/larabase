<?php

declare(strict_types=1);

namespace App\Actions\Role;

use App\Contracts\ActionInterface;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

final class DeleteRole implements ActionInterface
{
    /**
     * Delete a role.
     *
     * @param  array{id: int}  $data
     */
    public function handle(array $data): bool
    {
        return DB::transaction(function () use ($data): bool {
            $role = Role::findOrFail($data['id']);

            // Remove all permissions before deletion
            $role->syncPermissions([]);

            return (bool) $role->delete();
        });
    }
}
