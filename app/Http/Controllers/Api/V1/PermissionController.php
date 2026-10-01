<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\Permission\CreatePermission;
use App\Actions\Permission\DeletePermission;
use App\Actions\Permission\UpdatePermission;
use App\Http\Controllers\Controller;
use App\Http\Requests\Permission\StorePermissionRequest;
use App\Http\Requests\Permission\UpdatePermissionRequest;
use App\Http\Resources\PermissionResource;
use App\Http\Responses\ApiResponse;
use App\Queries\Permission\ListPermissionsQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function __construct(
        private readonly ListPermissionsQuery $listPermissions,
        private readonly CreatePermission $createPermission,
        private readonly UpdatePermission $updatePermission,
        private readonly DeletePermission $deletePermission,
    ) {}

    /**
     * Display a listing of permissions.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Permission::class);

        $permissions = $this->listPermissions->handle([
            'search' => $request->search,
            'group' => $request->group,
            'per_page' => $request->per_page ?? 15,
        ]);

        return ApiResponse::paginated(
            PermissionResource::collection($permissions)
        );
    }

    /**
     * Store a newly created permission.
     */
    public function store(StorePermissionRequest $request): JsonResponse
    {
        $permission = $this->createPermission->handle($request->validated());

        return ApiResponse::created(
            new PermissionResource($permission),
            __('permissions.created')
        );
    }

    /**
     * Display the specified permission.
     */
    public function show(Permission $permission): JsonResponse
    {
        $this->authorize('view', $permission);

        return ApiResponse::success(
            new PermissionResource($permission)
        );
    }

    /**
     * Update the specified permission.
     */
    public function update(UpdatePermissionRequest $request, Permission $permission): JsonResponse
    {
        $permission = $this->updatePermission->handle([...$request->validated(), 'id' => $permission->id]);

        return ApiResponse::success(
            new PermissionResource($permission),
            __('permissions.updated')
        );
    }

    /**
     * Remove the specified permission.
     */
    public function destroy(Permission $permission): JsonResponse
    {
        $this->authorize('delete', $permission);

        $this->deletePermission->handle(['id' => $permission->id]);

        return ApiResponse::success(null, __('permissions.deleted'));
    }
}
