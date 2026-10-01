<?php

declare(strict_types=1);

use App\Actions\Profile\UpdateAvatar;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->action = new UpdateAvatar;
    Storage::fake('public');
});

it('uploads avatar for user with existing profile', function () {
    skipIfNoGd();

    $user = createUserWithProfile();

    $file = fakeImage('avatar.png');

    $data = [
        'user_id' => $user->id,
        'avatar' => $file,
    ];

    $profile = $this->action->handle($data);

    expect($profile)->toBeInstanceOf(Profile::class)
        ->and($profile->getFirstMedia('avatar'))->not->toBeNull();
});

it('creates profile if it does not exist when uploading avatar', function () {
    skipIfNoGd();

    // Create user without triggering the auto-create in booted
    $user = User::factory()->make(['name' => 'Test User']);
    $user->saveQuietly();

    expect($user->profile)->toBeNull();

    $file = fakeImage('avatar.png');

    $data = [
        'user_id' => $user->id,
        'avatar' => $file,
    ];

    $profile = $this->action->handle($data);

    expect($profile)->toBeInstanceOf(Profile::class)
        ->and($profile->user_id)->toBe($user->id)
        ->and($profile->getFirstMedia('avatar'))->not->toBeNull();
});

it('replaces existing avatar with new one', function () {
    skipIfNoGd();

    $user = createUserWithProfile();
    $profile = $user->profile;

    // Upload first avatar
    $file1 = fakeImage('avatar1.png');
    $profile->addMedia($file1)->toMediaCollection('avatar');

    $originalMedia = $profile->getFirstMedia('avatar');
    expect($originalMedia)->not->toBeNull();

    // Upload new avatar
    $file2 = fakeImage('avatar2.png');

    $data = [
        'user_id' => $user->id,
        'avatar' => $file2,
    ];

    $updatedProfile = $this->action->handle($data);

    // Should have only one avatar
    expect($updatedProfile->getMedia('avatar'))->toHaveCount(1);
});

it('handles png images', function () {
    skipIfNoGd();

    $user = createUserWithProfile();

    $file = fakeImage('avatar.png');

    $data = [
        'user_id' => $user->id,
        'avatar' => $file,
    ];

    $profile = $this->action->handle($data);

    expect($profile->getFirstMedia('avatar'))->not->toBeNull();
});
