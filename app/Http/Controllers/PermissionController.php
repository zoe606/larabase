<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Permission\CreatePermission;
use App\Actions\Permission\DeletePermission;
use App\Actions\Permission\UpdatePermission;
use App\Http\Requests\Permission\StorePermissionRequest;
use App\Http\Requests\Permission\UpdatePermissionRequest;
use App\Queries\Permission\GetDistinctGroupsQuery;
use App\Queries\Permission\ListPermissionsQuery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function __construct(
        private readonly ListPermissionsQuery $listPermissions,
        private readonly GetDistinctGroupsQuery $getDistinctGroups,
        private readonly CreatePermission $createPermission,
        private readonly UpdatePermission $updatePermission,
        private readonly DeletePermission $deletePermission,
    ) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Permission::class);

        $permissions = $this->listPermissions->handle([
            'search' => $request->search,
            'group' => $request->group,
            'per_page' => 10,
        ]);

        return Inertia::render('permissions/Index', [
            'permissions' => $permissions->withQueryString(),
            'groups' => $this->getDistinctGroups->handle(),
            'filters' => $request->only('group', 'search'),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Permission::class);

        return Inertia::render('permissions/Form', [
            'groups' => $this->getDistinctGroups->handle(),
        ]);
    }

    public function store(StorePermissionRequest $request): RedirectResponse
    {
        $this->createPermission->handle($request->validated());

        return redirect()->route('permissions.index')
            ->with('success', __('permissions.created'));
    }

    public function edit(Permission $permission): Response
    {
        $this->authorize('update', $permission);

        return Inertia::render('permissions/Form', [
            'permission' => $permission,
            'groups' => $this->getDistinctGroups->handle(),
        ]);
    }

    public function update(UpdatePermissionRequest $request, Permission $permission): RedirectResponse
    {
        $this->updatePermission->handle([...$request->validated(), 'id' => $permission->id]);

        return redirect()->route('permissions.index')
            ->with('success', __('permissions.updated'));
    }

    public function destroy(Permission $permission): RedirectResponse
    {
        $this->authorize('delete', $permission);

        $this->deletePermission->handle(['id' => $permission->id]);

        return redirect()->route('permissions.index')
            ->with('success', __('permissions.deleted'));
    }
}
