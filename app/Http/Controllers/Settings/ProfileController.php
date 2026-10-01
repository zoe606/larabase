<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Actions\Profile\DeleteAvatar;
use App\Actions\Profile\UpdateAvatar;
use App\Actions\Profile\UpdateProfile;
use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\UpdateAvatarRequest;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Http\Resources\ProfileResource;
use App\Queries\Profile\GetProfileQuery;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function __construct(
        private readonly GetProfileQuery $getProfileQuery,
        private readonly UpdateProfile $updateProfile,
        private readonly UpdateAvatar $updateAvatar,
        private readonly DeleteAvatar $deleteAvatar,
    ) {}

    /**
     * Show the user's profile settings page.
     */
    public function edit(Request $request): Response
    {
        $user = $request->user();
        $profile = $this->getProfileQuery->handle(['user_id' => $user->id]);

        return Inertia::render('settings/profile', [
            'mustVerifyEmail' => $user instanceof MustVerifyEmail,
            'status' => $request->session()->get('status'),
            'profile' => $profile ? (new ProfileResource($profile))->resolve() : null,
        ]);
    }

    /**
     * Update the user's profile settings.
     */
    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        // Update email on user model
        if ($validated['email'] !== $user->email) {
            $user->email = $validated['email'];
            $user->email_verified_at = null;
            $user->save();
        }

        // Update profile
        $this->updateProfile->handle([
            'user_id' => $user->id,
            'name' => $validated['name'],
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'bio' => $validated['bio'] ?? null,
            'date_of_birth' => $validated['date_of_birth'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'timezone' => $validated['timezone'] ?? 'Asia/Jakarta',
            'locale' => $validated['locale'] ?? 'id',
            'website' => $validated['website'] ?? null,
            'twitter' => $validated['twitter'] ?? null,
            'linkedin' => $validated['linkedin'] ?? null,
            'github' => $validated['github'] ?? null,
        ]);

        return to_route('profile.edit');
    }

    /**
     * Update the user's avatar.
     */
    public function updateAvatar(UpdateAvatarRequest $request): RedirectResponse
    {
        $this->updateAvatar->handle([
            'user_id' => $request->user()->id,
            'avatar' => $request->file('avatar'),
        ]);

        return to_route('profile.edit');
    }

    /**
     * Delete the user's avatar.
     */
    public function deleteAvatar(Request $request): RedirectResponse
    {
        $this->deleteAvatar->handle([
            'user_id' => $request->user()->id,
        ]);

        return to_route('profile.edit');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
