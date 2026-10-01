<?php

declare(strict_types=1);

use App\Actions\Profile\UpdateProfile;
use App\Enums\Gender;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->action = new UpdateProfile;
});

it('updates a user profile with valid data', function () {
    $user = User::factory()->create(['name' => 'Original Name']);

    $data = [
        'user_id' => $user->id,
        'name' => 'Updated Name',
        'phone' => '+62 812 3456 7890',
        'address' => '123 Main St',
        'bio' => 'Test bio',
        'date_of_birth' => '1990-01-15',
        'gender' => 'male',
        'timezone' => 'Asia/Jakarta',
        'locale' => 'id',
        'website' => 'https://example.com',
        'twitter' => 'testuser',
        'linkedin' => 'testuser',
        'github' => 'testuser',
    ];

    $profile = $this->action->handle($data);

    expect($profile)->toBeInstanceOf(Profile::class)
        ->and($profile->name)->toBe('Updated Name')
        ->and($profile->phone)->toBe('+62 812 3456 7890')
        ->and($profile->address)->toBe('123 Main St')
        ->and($profile->bio)->toBe('Test bio')
        ->and($profile->date_of_birth->format('Y-m-d'))->toBe('1990-01-15')
        ->and($profile->gender)->toBe(Gender::MALE)
        ->and($profile->timezone)->toBe('Asia/Jakarta')
        ->and($profile->locale)->toBe('id')
        ->and($profile->website)->toBe('https://example.com')
        ->and($profile->twitter)->toBe('testuser')
        ->and($profile->linkedin)->toBe('testuser')
        ->and($profile->github)->toBe('testuser');
});

it('creates profile if it does not exist', function () {
    // Create user without triggering the auto-create in booted
    $user = User::factory()->make(['name' => 'Test User']);
    $user->saveQuietly();

    // Ensure no profile exists
    expect($user->profile)->toBeNull();

    $data = [
        'user_id' => $user->id,
        'name' => 'Test User',
    ];

    $profile = $this->action->handle($data);

    expect($profile)->toBeInstanceOf(Profile::class)
        ->and($profile->user_id)->toBe($user->id)
        ->and($profile->name)->toBe('Test User');
});

it('also updates user name for backward compatibility', function () {
    $user = User::factory()->create(['name' => 'Original Name']);

    $data = [
        'user_id' => $user->id,
        'name' => 'New Name',
    ];

    $this->action->handle($data);

    $user->refresh();

    expect($user->name)->toBe('New Name');
});

it('handles optional fields as null', function () {
    $user = User::factory()->create();

    $data = [
        'user_id' => $user->id,
        'name' => 'Test User',
    ];

    $profile = $this->action->handle($data);

    expect($profile->phone)->toBeNull()
        ->and($profile->address)->toBeNull()
        ->and($profile->bio)->toBeNull()
        ->and($profile->date_of_birth)->toBeNull()
        ->and($profile->gender)->toBeNull()
        ->and($profile->website)->toBeNull()
        ->and($profile->twitter)->toBeNull()
        ->and($profile->linkedin)->toBeNull()
        ->and($profile->github)->toBeNull();
});

it('uses default timezone and locale when not provided', function () {
    $user = User::factory()->create();

    $data = [
        'user_id' => $user->id,
        'name' => 'Test User',
    ];

    $profile = $this->action->handle($data);

    expect($profile->timezone)->toBe('Asia/Jakarta')
        ->and($profile->locale)->toBe('id');
});
