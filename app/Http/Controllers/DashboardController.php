<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

final class DashboardController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('dashboard', [
            'platform' => [
                'users' => User::query()->count(),
                'roles' => Role::query()->count(),
                'permissions' => Permission::query()->count(),
                'audit_events' => Activity::query()->count(),
            ],
        ]);
    }
}
