<?php

use App\Actions\User\ResetUserPassword;
use App\Events\UserPasswordReset;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->action = new ResetUserPassword;
});

it('resets user password with provided password', function () {
    $user = User::factory()->create();
    $originalPassword = $user->password;

    $data = [
        'id' => $user->id,
        'password' => 'newpassword123',
    ];

    $updatedUser = $this->action->handle($data);

    expect($updatedUser->password)->not->toBe($originalPassword)
        ->and(Hash::check('newpassword123', $updatedUser->password))->toBeTrue();
});

it('generates random password when not provided', function () {
    $user = User::factory()->create();
    $originalPassword = $user->password;

    $data = [
        'id' => $user->id,
    ];

    $updatedUser = $this->action->handle($data);

    expect($updatedUser->password)->not->toBe($originalPassword);
});

it('dispatches UserPasswordReset event', function () {
    Event::fake();

    $user = User::factory()->create();

    $this->action->handle(['id' => $user->id]);

    Event::assertDispatched(UserPasswordReset::class);
});

it('throws exception for non-existent user', function () {
    $this->action->handle(['id' => 99999]);
})->throws(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
