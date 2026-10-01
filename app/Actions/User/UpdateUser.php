<?php

declare(strict_types=1);

namespace App\Actions\User;

use App\Contracts\ActionInterface;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class UpdateUser implements ActionInterface
{
    /**
     * Update an existing user.
     *
     * @param  array{id: int, name: string, email: string, password?: string, roles: string[]}  $data
     */
    public function handle(array $data): User
    {
        return DB::transaction(function () use ($data): User {
            $user = User::findOrFail($data['id']);

            $user->update([
                'name' => $data['name'],
                'email' => $data['email'],
            ]);

            // Also update profile name if profile exists
            if ($user->profile) {
                $user->profile->update(['name' => $data['name']]);
            }

            if (! empty($data['password'])) {
                $user->update(['password' => Hash::make($data['password'])]);
            }

            $user->syncRoles($data['roles']);

            return $user->fresh(['roles', 'profile']);
        });
    }
}
