<?php

declare(strict_types=1);

namespace App\Queries\Profile;

use App\Contracts\QueryInterface;
use App\Models\Profile;

final class GetProfileQuery implements QueryInterface
{
    /**
     * Get a user's profile by user ID.
     *
     * @param  array{user_id?: int}  $filters
     */
    public function handle(array $filters = []): ?Profile
    {
        if (! isset($filters['user_id'])) {
            return null;
        }

        return Profile::query()
            ->with(['media'])
            ->where('user_id', $filters['user_id'])
            ->first();
    }
}
