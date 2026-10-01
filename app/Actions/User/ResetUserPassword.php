<?php

declare(strict_types=1);

namespace App\Actions\User;

use App\Contracts\ActionInterface;
use App\Events\UserPasswordReset;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

final class ResetUserPassword implements ActionInterface
{
    /**
     * Reset a user's password.
     *
     * @param  array{id: int, password?: string}  $data
     */
    public function handle(array $data): User
    {
        $user = User::findOrFail($data['id']);

        // Generate a random password if not provided
        $newPassword = $data['password'] ?? Str::random(12);

        $user->update([
            'password' => Hash::make($newPassword),
        ]);

        UserPasswordReset::dispatch($user, $newPassword);

        return $user;
    }
}
