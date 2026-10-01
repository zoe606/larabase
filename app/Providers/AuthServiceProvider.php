<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\MediaFolder;
use App\Models\Menu;
use App\Models\Profile;
use App\Models\User;
use App\Policies\MediaFolderPolicy;
use App\Policies\MenuPolicy;
use App\Policies\PermissionPolicy;
use App\Policies\ProfilePolicy;
use App\Policies\RolePolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        MediaFolder::class => MediaFolderPolicy::class,
        Menu::class => MenuPolicy::class,
        Permission::class => PermissionPolicy::class,
        Profile::class => ProfilePolicy::class,
        Role::class => RolePolicy::class,
        User::class => UserPolicy::class,
    ];

    public function boot(): void
    {
        Gate::before(function ($user, $ability) {
            return $user->hasRole('admin') ? true : null;
        });
    }
}
