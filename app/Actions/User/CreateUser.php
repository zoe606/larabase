<?php

declare(strict_types=1);

namespace App\Actions\User;

use App\Contracts\ActionInterface;
use App\Events\UserCreated;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class CreateUser implements ActionInterface
{
    /**
     * Create a new user.
     *
     * @param  array{name: string, email: string, password: string, roles: string[]}  $data
     */
    public function handle(array $data): User
    {
        return DB::transaction(function () use ($data): User {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            $user->syncRoles($data['roles']);

            UserCreated::dispatch($user, $data['password']);

            return $user->load(['roles', 'profile']);
        });
    }
}
