<?php

declare(strict_types=1);

use App\Policies\ProfilePolicy;

beforeEach(function () {
    $this->policy = new ProfilePolicy;
});

it('allows user to view their own profile', function () {
    $user = createUserWithProfile();
    $profile = $user->profile;

    $result = $this->policy->view($user, $profile);

    expect($result)->toBeTrue();
});

it('denies user from viewing another users profile', function () {
    $user1 = createUserWithProfile();
    $user2 = createUserWithProfile();
    $profile = $user2->profile;

    $result = $this->policy->view($user1, $profile);

    expect($result)->toBeFalse();
});

it('allows user to update their own profile', function () {
    $user = createUserWithProfile();
    $profile = $user->profile;

    $result = $this->policy->update($user, $profile);

    expect($result)->toBeTrue();
});

it('denies user from updating another users profile', function () {
    $user1 = createUserWithProfile();
    $user2 = createUserWithProfile();
    $profile = $user2->profile;

    $result = $this->policy->update($user1, $profile);

    expect($result)->toBeFalse();
});

it('allows user to delete their own profile', function () {
    $user = createUserWithProfile();
    $profile = $user->profile;

    $result = $this->policy->delete($user, $profile);

    expect($result)->toBeTrue();
});

it('denies user from deleting another users profile', function () {
    $user1 = createUserWithProfile();
    $user2 = createUserWithProfile();
    $profile = $user2->profile;

    $result = $this->policy->delete($user1, $profile);

    expect($result)->toBeFalse();
});
