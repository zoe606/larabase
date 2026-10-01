<?php

declare(strict_types=1);

namespace App\Actions\Profile;

use App\Contracts\ActionInterface;
use App\Exceptions\Profile\ProfileNotFoundException;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class DeleteAvatar implements ActionInterface
{
    /**
     * Delete the user's avatar.
     *
     * @param  array{user_id: int}  $data
     */
    public function handle(array $data): Profile
    {
        return DB::transaction(function () use ($data): Profile {
            $user = User::findOrFail($data['user_id']);

            // Get profile
            $profile = $user->profile;

            if (! $profile) {
                throw new ProfileNotFoundException;
            }

            // Clear avatar collection
            $profile->clearMediaCollection('avatar');

            return $profile->fresh();
        });
    }
}
