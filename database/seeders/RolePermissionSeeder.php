<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Permission as PlatformPermission;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $admin = Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'user']);

        $permissions = [];
        foreach (PlatformPermission::cases() as $permission) {
            $permissions[$permission->group()][] = $permission->value;
        }

        $permissions['Dashboard'] = ['dashboard-view'];
        $permissions['Access'] = ['access-view', 'permission-view'];
        $permissions['Settings'] = ['settings-view', 'menu-view', 'app-settings-view', 'backup-view'];
        $permissions['Utilities'] = ['utilities-view', 'log-view', 'filemanager-view'];

        foreach ($permissions as $group => $names) {
            foreach ($names as $name) {
                $permission = Permission::firstOrCreate(['name' => $name], ['group' => $group]);

                if ($permission->group !== $group) {
                    $permission->update(['group' => $group]);
                }

                if (! $admin->hasPermissionTo($permission)) {
                    $admin->givePermissionTo($permission);
                }
            }
        }
    }
}
