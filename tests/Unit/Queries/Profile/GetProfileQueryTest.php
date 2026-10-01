<?php

declare(strict_types=1);

use App\Models\Profile;
use App\Models\User;
use App\Queries\Profile\GetProfileQuery;

beforeEach(function () {
    $this->query = new GetProfileQuery;
});

it('returns profile for given user id', function () {
    $user = createUserWithProfile();
    $profile = $user->profile;
    $profile->update([
        'name' => 'Test User',
        'phone' => '1234567890',
    ]);

    $result = $this->query->handle(['user_id' => $user->id]);

    expect($result)->toBeInstanceOf(Profile::class)
        ->and($result->id)->toBe($profile->id)
        ->and($result->name)->toBe('Test User')
        ->and($result->phone)->toBe('1234567890');
});

it('returns null if user has no profile', function () {
    // Create user without profile
    $user = User::factory()->make();
    $user->saveQuietly();

    $result = $this->query->handle(['user_id' => $user->id]);

    expect($result)->toBeNull();
});

it('returns null if user_id is not provided', function () {
    $result = $this->query->handle([]);

    expect($result)->toBeNull();
});

it('returns null for non-existent user', function () {
    $result = $this->query->handle(['user_id' => 99999]);

    expect($result)->toBeNull();
});

it('eager loads media relationship', function () {
    $user = createUserWithProfile();

    $result = $this->query->handle(['user_id' => $user->id]);

    expect($result->relationLoaded('media'))->toBeTrue();
});
