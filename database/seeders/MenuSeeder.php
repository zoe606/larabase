<?php

namespace Database\Seeders;

use App\Models\Menu;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        // MENU: Dashboard
        Menu::updateOrCreate(
            ['title' => 'Dashboard'],
            [
                'icon' => 'Home',
                'route' => '/dashboard',
                'order' => 1,
                'permission_name' => 'dashboard-view',
            ]
        );

        // GROUP: Access
        $access = Menu::updateOrCreate(
            ['title' => 'Access'],
            [
                'icon' => 'Contact',
                'route' => '#',
                'order' => 2,
                'permission_name' => 'access-view',
            ]
        );

        Menu::updateOrCreate(
            ['title' => 'Permissions'],
            [
                'icon' => 'AlertOctagon',
                'route' => '/permissions',
                'order' => 2,
                'permission_name' => 'permission-view',
                'parent_id' => $access->id,
            ]
        );

        Menu::updateOrCreate(
            ['title' => 'Users'],
            [
                'icon' => 'Users',
                'route' => '/users',
                'order' => 3,
                'permission_name' => 'users-view',
                'parent_id' => $access->id,
            ]
        );

        Menu::updateOrCreate(
            ['title' => 'Roles'],
            [
                'icon' => 'AlertTriangle',
                'route' => '/roles',
                'order' => 4,
                'permission_name' => 'roles-view',
                'parent_id' => $access->id,
            ]
        );

        // GROUP: Settings
        $settings = Menu::updateOrCreate(
            ['title' => 'Settings'],
            [
                'icon' => 'Settings',
                'route' => '#',
                'order' => 3,
                'permission_name' => 'settings-view',
            ]
        );

        Menu::updateOrCreate(
            ['title' => 'Menu Manager'],
            [
                'icon' => 'Menu',
                'route' => '/menus',
                'order' => 1,
                'permission_name' => 'menu-view',
                'parent_id' => $settings->id,
            ]
        );

        Menu::updateOrCreate(
            ['title' => 'App Settings'],
            [
                'icon' => 'AtSign',
                'route' => '/settingsapp',
                'order' => 2,
                'permission_name' => 'app-settings-view',
                'parent_id' => $settings->id,
            ]
        );

        Menu::updateOrCreate(
            ['title' => 'Backup'],
            [
                'icon' => 'Inbox',
                'route' => '/backup',
                'order' => 3,
                'permission_name' => 'backup-view',
                'parent_id' => $settings->id,
            ]
        );

        // GROUP: Utilities
        $utilities = Menu::updateOrCreate(
            ['title' => 'Utilities'],
            [
                'icon' => 'Wrench',
                'route' => '#',
                'order' => 4,
                'permission_name' => 'utilities-view',
            ]
        );

        Menu::updateOrCreate(
            ['title' => 'Audit Logs'],
            [
                'icon' => 'Activity',
                'route' => '/audit-logs',
                'order' => 2,
                'permission_name' => 'log-view',
                'parent_id' => $utilities->id,
            ]
        );

        Menu::updateOrCreate(
            ['title' => 'File Manager'],
            [
                'icon' => 'Folder',
                'route' => '/files',
                'order' => 3,
                'permission_name' => 'filemanager-view',
                'parent_id' => $utilities->id,
            ]
        );

        $permissions = Menu::pluck('permission_name')->unique()->filter();

        foreach ($permissions as $permName) {
            Permission::firstOrCreate(['name' => $permName]);
        }

        $role = Role::firstOrCreate(['name' => 'user']);
        if (! $role->hasPermissionTo('dashboard-view')) {
            $role->givePermissionTo('dashboard-view');
        }
    }
}
