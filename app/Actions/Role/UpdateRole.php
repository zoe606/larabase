<?php

declare(strict_types=1);

namespace App\Actions\Role;

use App\Contracts\ActionInterface;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

final class UpdateRole implements ActionInterface
{
    /**
     * Update an existing role.
     *
     * @param  array{id: int, name: string, permissions?: string[]}  $data
     */
    public function handle(array $data): Role
    {
        return DB::transaction(function () use ($data): Role {
            $role = Role::findOrFail($data['id']);

            $role->update([
                'name' => $data['name'],
            ]);

            $role->syncPermissions($data['permissions'] ?? []);

            return $role->fresh(['permissions']);
        });
    }
}
