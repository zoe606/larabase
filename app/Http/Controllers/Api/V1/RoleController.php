<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\Role\CreateRole;
use App\Actions\Role\DeleteRole;
use App\Actions\Role\UpdateRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Role\StoreRoleRequest;
use App\Http\Requests\Role\UpdateRoleRequest;
use App\Http\Resources\RoleResource;
use App\Http\Responses\ApiResponse;
use App\Queries\Role\ListRolesQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function __construct(
        private readonly ListRolesQuery $listRoles,
        private readonly CreateRole $createRole,
        private readonly UpdateRole $updateRole,
        private readonly DeleteRole $deleteRole,
    ) {}

    /**
     * Display a listing of roles.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Role::class);

        $roles = $this->listRoles->handle([
            'search' => $request->search,
            'per_page' => $request->per_page ?? 15,
        ]);

        return ApiResponse::paginated(
            RoleResource::collection($roles)
        );
    }

    /**
     * Store a newly created role.
     */
    public function store(StoreRoleRequest $request): JsonResponse
    {
        $role = $this->createRole->handle($request->validated());

        return ApiResponse::created(
            new RoleResource($role->load('permissions')),
            __('roles.created')
        );
    }

    /**
     * Display the specified role.
     */
    public function show(Role $role): JsonResponse
    {
        $this->authorize('view', $role);

        return ApiResponse::success(
            new RoleResource($role->load('permissions'))
        );
    }

    /**
     * Update the specified role.
     */
    public function update(UpdateRoleRequest $request, Role $role): JsonResponse
    {
        $role = $this->updateRole->handle([...$request->validated(), 'id' => $role->id]);

        return ApiResponse::success(
            new RoleResource($role->load('permissions')),
            __('roles.updated')
        );
    }

    /**
     * Remove the specified role.
     */
    public function destroy(Role $role): JsonResponse
    {
        $this->authorize('delete', $role);

        $this->deleteRole->handle(['id' => $role->id]);

        return ApiResponse::success(null, __('roles.deleted'));
    }
}
