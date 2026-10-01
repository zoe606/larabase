<?php

declare(strict_types=1);

use App\Models\Profile;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
});

describe('profile settings page', function () {
    it('shows profile edit page for authenticated user', function () {
        $user = createUserWithProfile();
        $user->profile->update(['name' => 'Test User']);

        $response = $this->actingAs($user)->get(route('profile.edit'));

        $response->assertOk()
            ->assertInertia(
                fn ($page) => $page
                    ->component('settings/profile')
                    ->has('profile')
                    ->where('profile.name', 'Test User')
            );
    });

    it('redirects guests to login', function () {
        $response = $this->get(route('profile.edit'));

        $response->assertRedirect(route('login'));
    });

    it('handles users without profiles gracefully', function () {
        // Create user without auto-created profile
        $user = User::factory()->make();
        $user->saveQuietly();

        $response = $this->actingAs($user)->get(route('profile.edit'));

        $response->assertOk()
            ->assertInertia(
                fn ($page) => $page
                    ->component('settings/profile')
                    ->where('profile', null)
            );
    });
});

describe('profile update', function () {
    it('updates profile with valid data', function () {
        $user = createUserWithProfile(['email' => 'original@example.com']);

        $response = $this->actingAs($user)->patch(route('profile.update'), [
            'name' => 'Updated Name',
            'email' => 'original@example.com',
            'phone' => '+62 812 1234 5678',
            'bio' => 'My bio',
            'timezone' => 'Asia/Jakarta',
            'locale' => 'id',
        ]);

        $response->assertRedirect(route('profile.edit'));

        $user->refresh();
        expect($user->profile->name)->toBe('Updated Name')
            ->and($user->profile->phone)->toBe('+62 812 1234 5678')
            ->and($user->profile->bio)->toBe('My bio');
    });

    it('validates required name field', function () {
        $user = createUserWithProfile();

        $response = $this->actingAs($user)->patch(route('profile.update'), [
            'name' => '',
            'email' => $user->email,
        ]);

        $response->assertSessionHasErrors(['name']);
    });

    it('validates required email field', function () {
        $user = createUserWithProfile();

        $response = $this->actingAs($user)->patch(route('profile.update'), [
            'name' => 'Test',
            'email' => '',
        ]);

        $response->assertSessionHasErrors(['email']);
    });

    it('validates email uniqueness excluding current user', function () {
        $user1 = createUserWithProfile(['email' => 'user1@example.com']);
        createUserWithProfile(['email' => 'user2@example.com']);

        $response = $this->actingAs($user1)->patch(route('profile.update'), [
            'name' => 'Test',
            'email' => 'user2@example.com',
        ]);

        $response->assertSessionHasErrors(['email']);
    });

    it('allows user to keep their own email', function () {
        $user = createUserWithProfile(['email' => 'user@example.com']);

        $response = $this->actingAs($user)->patch(route('profile.update'), [
            'name' => 'Test',
            'email' => 'user@example.com',
        ]);

        $response->assertRedirect(route('profile.edit'));
    });

    it('resets email verification when email changes', function () {
        $user = createUserWithProfile([
            'email' => 'old@example.com',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->patch(route('profile.update'), [
            'name' => 'Test',
            'email' => 'new@example.com',
        ]);

        $user->refresh();
        expect($user->email)->toBe('new@example.com')
            ->and($user->email_verified_at)->toBeNull();
    });

    it('validates date of birth is before today', function () {
        $user = createUserWithProfile();

        $response = $this->actingAs($user)->patch(route('profile.update'), [
            'name' => 'Test',
            'email' => $user->email,
            'date_of_birth' => now()->addDay()->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors(['date_of_birth']);
    });

    it('validates gender enum values', function () {
        $user = createUserWithProfile();

        $response = $this->actingAs($user)->patch(route('profile.update'), [
            'name' => 'Test',
            'email' => $user->email,
            'gender' => 'invalid',
        ]);

        $response->assertSessionHasErrors(['gender']);
    });

    it('validates website url format', function () {
        $user = createUserWithProfile();

        $response = $this->actingAs($user)->patch(route('profile.update'), [
            'name' => 'Test',
            'email' => $user->email,
            'website' => 'not-a-url',
        ]);

        $response->assertSessionHasErrors(['website']);
    });
});

describe('avatar upload', function () {
    it('uploads avatar successfully', function () {
        skipIfNoGd();

        $user = createUserWithProfile();
        $file = fakeImage('avatar.png');

        $response = $this->actingAs($user)->post(route('profile.avatar.update'), [
            'avatar' => $file,
        ]);

        $response->assertRedirect(route('profile.edit'));

        $user->refresh();
        expect($user->profile->getFirstMedia('avatar'))->not->toBeNull();
    });

    it('validates avatar is required', function () {
        $user = createUserWithProfile();

        $response = $this->actingAs($user)->post(route('profile.avatar.update'), []);

        $response->assertSessionHasErrors(['avatar']);
    });

    it('validates avatar is an image', function () {
        $user = createUserWithProfile();
        // Create a non-image file
        $tempFile = tempnam(sys_get_temp_dir(), 'test_');
        file_put_contents($tempFile, 'not an image');
        $file = new Illuminate\Http\UploadedFile($tempFile, 'document.pdf', 'application/pdf', null, true);

        $response = $this->actingAs($user)->post(route('profile.avatar.update'), [
            'avatar' => $file,
        ]);

        $response->assertSessionHasErrors(['avatar']);
    });

    it('validates avatar max size is 2MB', function () {
        $user = createUserWithProfile();
        // Create a file larger than 2MB (3MB = 3145728 bytes)
        $tempFile = tempnam(sys_get_temp_dir(), 'test_');
        file_put_contents($tempFile, str_repeat('x', 3 * 1024 * 1024));
        $file = new Illuminate\Http\UploadedFile($tempFile, 'large.png', 'image/png', null, true);

        $response = $this->actingAs($user)->post(route('profile.avatar.update'), [
            'avatar' => $file,
        ]);

        $response->assertSessionHasErrors(['avatar']);
    });
});

describe('avatar delete', function () {
    it('deletes avatar successfully', function () {
        skipIfNoGd();

        $user = createUserWithProfile();
        $profile = $user->profile;

        // Add avatar
        $file = fakeImage('avatar.png');
        $profile->addMedia($file)->toMediaCollection('avatar');

        expect($profile->getFirstMedia('avatar'))->not->toBeNull();

        $response = $this->actingAs($user)->delete(route('profile.avatar.delete'));

        $response->assertRedirect(route('profile.edit'));

        $profile->refresh();
        expect($profile->getFirstMedia('avatar'))->toBeNull();
    });
});

describe('account deletion', function () {
    it('deletes user account with correct password', function () {
        $user = createUserWithProfile();

        $response = $this->actingAs($user)->delete(route('profile.destroy'), [
            'password' => 'password',
        ]);

        $response->assertRedirect('/');

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    });

    it('rejects incorrect password', function () {
        $user = createUserWithProfile();

        $response = $this->actingAs($user)->delete(route('profile.destroy'), [
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors(['password']);
        $this->assertAuthenticatedAs($user);
    });
});
