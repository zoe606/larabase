<?php

declare(strict_types=1);

use App\Enums\Gender;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
});

it('belongs to a user', function () {
    $user = createUserWithProfile();
    $profile = $user->profile;

    expect($profile->user)->toBeInstanceOf(User::class)
        ->and($profile->user->id)->toBe($user->id);
});

it('has fillable attributes', function () {
    $data = [
        'user_id' => 1,
        'name' => 'Test User',
        'phone' => '1234567890',
        'address' => '123 Main St',
        'bio' => 'Test bio',
        'date_of_birth' => '1990-01-15',
        'gender' => 'male',
        'timezone' => 'UTC',
        'locale' => 'en',
        'website' => 'https://example.com',
        'twitter' => 'testuser',
        'linkedin' => 'testuser',
        'github' => 'testuser',
    ];

    $profile = new Profile($data);

    expect($profile->name)->toBe('Test User')
        ->and($profile->phone)->toBe('1234567890')
        ->and($profile->address)->toBe('123 Main St')
        ->and($profile->bio)->toBe('Test bio')
        ->and($profile->gender)->toBe(Gender::MALE)
        ->and($profile->timezone)->toBe('UTC')
        ->and($profile->locale)->toBe('en')
        ->and($profile->website)->toBe('https://example.com')
        ->and($profile->twitter)->toBe('testuser')
        ->and($profile->linkedin)->toBe('testuser')
        ->and($profile->github)->toBe('testuser');
});

it('casts date_of_birth to date', function () {
    $user = createUserWithProfile();
    $profile = $user->profile;
    $profile->update(['date_of_birth' => '1990-01-15']);

    expect($profile->date_of_birth)->toBeInstanceOf(Illuminate\Support\Carbon::class)
        ->and($profile->date_of_birth->format('Y-m-d'))->toBe('1990-01-15');
});

it('returns avatar url when avatar exists', function () {
    skipIfNoGd();

    $user = createUserWithProfile();
    $profile = $user->profile;

    $file = fakeImage('avatar.png');
    $profile->addMedia($file)->toMediaCollection('avatar');

    expect($profile->avatar_url)->not->toBeNull();
});

it('returns null avatar url when no avatar', function () {
    $user = createUserWithProfile();
    $profile = $user->profile;

    expect($profile->avatar_url)->toBeNull();
});

it('returns medium avatar url', function () {
    skipIfNoGd();

    $user = createUserWithProfile();
    $profile = $user->profile;

    $file = fakeImage('avatar.png');
    $profile->addMedia($file)->toMediaCollection('avatar');

    expect($profile->avatar_medium_url)->not->toBeNull();
});

it('returns original avatar url', function () {
    skipIfNoGd();

    $user = createUserWithProfile();
    $profile = $user->profile;

    $file = fakeImage('avatar.png');
    $profile->addMedia($file)->toMediaCollection('avatar');

    expect($profile->avatar_original_url)->not->toBeNull();
});

it('only keeps one avatar at a time', function () {
    skipIfNoGd();

    $user = createUserWithProfile();
    $profile = $user->profile;

    // Add first avatar
    $file1 = fakeImage('avatar1.png');
    $profile->addMedia($file1)->toMediaCollection('avatar');

    // Add second avatar
    $file2 = fakeImage('avatar2.png');
    $profile->addMedia($file2)->toMediaCollection('avatar');

    // Should only have one avatar (singleFile collection)
    expect($profile->getMedia('avatar'))->toHaveCount(1);
});
