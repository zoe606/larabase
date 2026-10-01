<?php

declare(strict_types=1);

namespace App\Actions\Profile;

use App\Contracts\ActionInterface;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

final class UpdateAvatar implements ActionInterface
{
    /**
     * Update the user's avatar.
     *
     * @param  array{user_id: int, avatar: UploadedFile}  $data
     */
    public function handle(array $data): Profile
    {
        return DB::transaction(function () use ($data): Profile {
            $user = User::findOrFail($data['user_id']);

            // Get or create profile
            $profile = $user->profile ?? $user->profile()->create([
                'name' => $user->name,
            ]);

            // Clear existing avatar and add new one
            $profile->clearMediaCollection('avatar');
            $profile->addMedia($data['avatar'])
                ->toMediaCollection('avatar');

            return $profile->fresh();
        });
    }
}
