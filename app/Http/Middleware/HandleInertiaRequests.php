<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\SettingApp;
use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     */
    public function share(Request $request): array
    {
        [$message, $author] = str(Inspiring::quotes()->random())->explode('-');

        return array_merge(parent::share($request), [
            'name' => config('app.name'),
            'quote' => ['message' => trim($message), 'author' => trim($author)],
            'auth' => [
                'user' => fn () => $this->getAuthUser($request),
            ],
            'flash' => [
                'success' => session('success'),
                'error' => session('error'),
            ],
            'setting' => fn () => SettingApp::first(),
            'unreadNotificationCount' => fn () => $request->user()?->unreadNotifications()->count() ?? 0,
        ]);
    }

    /**
     * Get the authenticated user with profile data.
     *
     * @return array<string, mixed>|null
     */
    private function getAuthUser(Request $request): ?array
    {
        $user = $request->user();

        if (! $user) {
            return null;
        }

        // Load profile relationship if not already loaded
        $user->loadMissing('profile.media');

        return [
            'id' => $user->id,
            'name' => $user->profile->name ?? $user->name,
            'email' => $user->email,
            'email_verified_at' => $user->email_verified_at?->toISOString(),
            'avatar' => $user->profile?->avatar_url,
            'created_at' => $user->created_at?->toISOString(),
            'updated_at' => $user->updated_at?->toISOString(),
        ];
    }
}
