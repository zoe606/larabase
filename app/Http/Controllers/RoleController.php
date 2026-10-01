<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Role\CreateRole;
use App\Actions\Role\DeleteRole;
use App\Actions\Role\UpdateRole;
use App\Http\Requests\Role\StoreRoleRequest;
use App\Http\Requests\Role\UpdateRoleRequest;
use App\Queries\Permission\GetPermissionGroupsQuery;
use App\Queries\Role\ListRolesQuery;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function __construct(
        private readonly ListRolesQuery $listRoles,
        private readonly GetPermissionGroupsQuery $getPermissionGroups,
        private readonly CreateRole $createRole,
        private readonly UpdateRole $updateRole,
        private readonly DeleteRole $deleteRole,
    ) {}

    public function index(): Response
    {
        $this->authorize('viewAny', Role::class);

        $roles = $this->listRoles->handle(['paginate' => false]);
        $permissions = $this->getPermissionGroups->handle([]);

        return Inertia::render('roles/Index', [
            'roles' => $roles,
            'groupedPermissions' => $permissions,
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Role::class);

        $permissions = $this->getPermissionGroups->handle([]);

        return Inertia::render('roles/Form', [
            'groupedPermissions' => $permissions,
        ]);
    }

    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $this->createRole->handle($request->validated());

        return redirect()->route('roles.index')
            ->with('success', __('roles.created'));
    }

    public function edit(Role $role): Response
    {
        $this->authorize('update', $role);

        $permissions = $this->getPermissionGroups->handle([]);
        $role->load('permissions');

        return Inertia::render('roles/Form', [
            'role' => $role,
            'groupedPermissions' => $permissions,
        ]);
    }

    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        $this->updateRole->handle([...$request->validated(), 'id' => $role->id]);

        return redirect()->route('roles.index')
            ->with('success', __('roles.updated'));
    }

    public function destroy(Role $role): RedirectResponse
    {
        $this->authorize('delete', $role);

        $this->deleteRole->handle(['id' => $role->id]);

        return redirect()->route('roles.index')
            ->with('success', __('roles.deleted'));
    }
}
