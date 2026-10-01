<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\User\CreateUser;
use App\Actions\User\DeleteUser;
use App\Actions\User\ResetUserPassword;
use App\Actions\User\UpdateUser;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\User;
use App\Queries\Role\ListRolesQuery;
use App\Queries\User\ListUsersQuery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function __construct(
        private readonly ListUsersQuery $listUsers,
        private readonly ListRolesQuery $listRoles,
        private readonly CreateUser $createUser,
        private readonly UpdateUser $updateUser,
        private readonly DeleteUser $deleteUser,
        private readonly ResetUserPassword $resetPassword,
    ) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', User::class);

        $users = $this->listUsers->handle([
            'search' => $request->search,
            'per_page' => $request->per_page ?? 10,
        ]);

        return Inertia::render('users/Index', [
            'users' => $users,
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', User::class);

        $roles = $this->listRoles->handle(['paginate' => false]);

        return Inertia::render('users/Form', [
            'roles' => $roles,
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $this->createUser->handle($request->validated());

        return redirect()->route('users.index')
            ->with('success', __('users.created'));
    }

    public function edit(User $user): Response
    {
        $this->authorize('update', $user);

        $roles = $this->listRoles->handle(['paginate' => false]);

        return Inertia::render('users/Form', [
            'user' => $user->only(['id', 'name', 'email']),
            'roles' => $roles,
            'currentRoles' => $user->roles->pluck('name')->toArray(),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->updateUser->handle([...$request->validated(), 'id' => $user->id]);

        return redirect()->route('users.index')
            ->with('success', __('users.updated'));
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        $this->deleteUser->handle(['id' => $user->id]);

        return redirect()->route('users.index')
            ->with('success', __('users.deleted'));
    }

    public function resetPassword(User $user): RedirectResponse
    {
        $this->authorize('resetPassword', $user);

        $this->resetPassword->handle(['id' => $user->id]);

        return redirect()->back()
            ->with('success', __('users.password_reset'));
    }
}
