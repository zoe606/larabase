<?php

declare(strict_types=1);

namespace App\Actions\Profile;

use App\Contracts\ActionInterface;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class UpdateProfile implements ActionInterface
{
    /**
     * Update the user's profile.
     *
     * @param  array{
     *     user_id: int,
     *     name: string,
     *     phone?: string|null,
     *     address?: string|null,
     *     bio?: string|null,
     *     date_of_birth?: string|null,
     *     gender?: string|null,
     *     timezone?: string,
     *     locale?: string,
     *     website?: string|null,
     *     twitter?: string|null,
     *     linkedin?: string|null,
     *     github?: string|null
     * }  $data
     */
    public function handle(array $data): Profile
    {
        return DB::transaction(function () use ($data): Profile {
            $user = User::findOrFail($data['user_id']);

            // Get or create profile
            $profile = $user->profile ?? $user->profile()->create([
                'name' => $user->name,
            ]);

            // Update profile fields
            $profile->update([
                'name' => $data['name'],
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
                'bio' => $data['bio'] ?? null,
                'date_of_birth' => $data['date_of_birth'] ?? null,
                'gender' => $data['gender'] ?? null,
                'timezone' => $data['timezone'] ?? 'Asia/Jakarta',
                'locale' => $data['locale'] ?? 'id',
                'website' => $data['website'] ?? null,
                'twitter' => $data['twitter'] ?? null,
                'linkedin' => $data['linkedin'] ?? null,
                'github' => $data['github'] ?? null,
            ]);

            // Also update the user's name for backward compatibility
            $user->update(['name' => $data['name']]);

            return $profile->fresh();
        });
    }
}
