<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\User\CreateUser;
use App\Actions\User\DeleteUser;
use App\Actions\User\ResetUserPassword;
use App\Actions\User\UpdateUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use App\Queries\User\ListUsersQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(
        private readonly ListUsersQuery $listUsers,
        private readonly CreateUser $createUser,
        private readonly UpdateUser $updateUser,
        private readonly DeleteUser $deleteUser,
        private readonly ResetUserPassword $resetPassword,
    ) {}

    /**
     * Display a listing of users.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', User::class);

        $users = $this->listUsers->handle([
            'search' => $request->search,
            'role' => $request->role,
            'per_page' => $request->per_page ?? 15,
        ]);

        return ApiResponse::paginated(
            UserResource::collection($users)
        );
    }

    /**
     * Store a newly created user.
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = $this->createUser->handle($request->validated());

        return ApiResponse::created(
            new UserResource($user->load('roles')),
            __('users.created')
        );
    }

    /**
     * Display the specified user.
     */
    public function show(User $user): JsonResponse
    {
        $this->authorize('view', $user);

        return ApiResponse::success(
            new UserResource($user->load('roles', 'permissions'))
        );
    }

    /**
     * Update the specified user.
     */
    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $user = $this->updateUser->handle([...$request->validated(), 'id' => $user->id]);

        return ApiResponse::success(
            new UserResource($user->load('roles')),
            __('users.updated')
        );
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user): JsonResponse
    {
        $this->authorize('delete', $user);

        $this->deleteUser->handle(['id' => $user->id]);

        return ApiResponse::success(null, __('users.deleted'));
    }

    /**
     * Reset user password.
     */
    public function resetPassword(User $user): JsonResponse
    {
        $this->authorize('resetPassword', $user);

        $this->resetPassword->handle(['id' => $user->id]);

        return ApiResponse::success(null, __('users.password_reset'));
    }
}
