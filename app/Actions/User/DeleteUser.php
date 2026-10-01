<?php

declare(strict_types=1);

namespace App\Actions\User;

use App\Contracts\ActionInterface;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class DeleteUser implements ActionInterface
{
    /**
     * Delete a user.
     *
     * @param  array{id: int}  $data
     */
    public function handle(array $data): bool
    {
        return DB::transaction(function () use ($data): bool {
            $user = User::findOrFail($data['id']);

            // Remove all roles before deletion
            $user->syncRoles([]);

            return (bool) $user->delete();
        });
    }
}
