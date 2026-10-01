<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProfilePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the profile.
     */
    public function view(User $user, Profile $profile): bool
    {
        return $user->id === $profile->user_id;
    }

    /**
     * Determine whether the user can update the profile.
     */
    public function update(User $user, Profile $profile): bool
    {
        return $user->id === $profile->user_id;
    }

    /**
     * Determine whether the user can delete the profile.
     */
    public function delete(User $user, Profile $profile): bool
    {
        return $user->id === $profile->user_id;
    }
}
