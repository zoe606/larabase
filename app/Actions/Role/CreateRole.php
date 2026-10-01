<?php

declare(strict_types=1);

namespace App\Actions\Role;

use App\Contracts\ActionInterface;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

final class CreateRole implements ActionInterface
{
    /**
     * Create a new role.
     *
     * @param  array{name: string, permissions?: string[]}  $data
     */
    public function handle(array $data): Role
    {
        return DB::transaction(function () use ($data): Role {
            $role = Role::create([
                'name' => $data['name'],
                'guard_name' => 'web',
            ]);

            if (! empty($data['permissions'])) {
                $role->syncPermissions($data['permissions']);
            }

            return $role->load('permissions');
        });
    }
}
