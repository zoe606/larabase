<?php

declare(strict_types=1);

use App\Actions\Profile\DeleteAvatar;
use App\Exceptions\Profile\ProfileNotFoundException;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->action = new DeleteAvatar;
    Storage::fake('public');
});

it('deletes avatar from profile', function () {
    skipIfNoGd();

    $user = createUserWithProfile();
    $profile = $user->profile;

    // Add avatar using helper function
    $file = fakeImage('avatar.png');
    $profile->addMedia($file)->toMediaCollection('avatar');

    expect($profile->getFirstMedia('avatar'))->not->toBeNull();

    $data = [
        'user_id' => $user->id,
    ];

    $updatedProfile = $this->action->handle($data);

    expect($updatedProfile->getFirstMedia('avatar'))->toBeNull();
});

it('throws exception if profile does not exist', function () {
    // Create user without profile
    $user = User::factory()->make();
    $user->saveQuietly();

    $data = [
        'user_id' => $user->id,
    ];

    $this->action->handle($data);
})->throws(ProfileNotFoundException::class, 'Profile not found.');

it('handles deleting when no avatar exists', function () {
    $user = createUserWithProfile();
    $profile = $user->profile;

    // No avatar uploaded
    expect($profile->getFirstMedia('avatar'))->toBeNull();

    $data = [
        'user_id' => $user->id,
    ];

    $updatedProfile = $this->action->handle($data);

    // Should not throw, just return profile
    expect($updatedProfile)->toBeInstanceOf(Profile::class)
        ->and($updatedProfile->getFirstMedia('avatar'))->toBeNull();
});
