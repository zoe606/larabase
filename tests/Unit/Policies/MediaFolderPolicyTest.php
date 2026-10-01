<?php

declare(strict_types=1);

use App\Models\MediaFolder;
use App\Models\User;
use App\Policies\MediaFolderPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->folder = MediaFolder::factory()->create(['user_id' => $this->user->id]);
    $this->policy = new MediaFolderPolicy;
});

describe('viewAny', function () {
    it('always denies', function () {
        expect($this->policy->viewAny($this->user))->toBeFalse();
    });
});

describe('view', function () {
    it('always denies', function () {
        expect($this->policy->view($this->user, $this->folder))->toBeFalse();
    });
});

describe('create', function () {
    it('always denies', function () {
        expect($this->policy->create($this->user))->toBeFalse();
    });
});

describe('update', function () {
    it('allows the owner', function () {
        expect($this->policy->update($this->user, $this->folder))->toBeTrue();
    });
});

describe('delete', function () {
    it('allows owner to delete', function () {
        expect($this->policy->delete($this->user, $this->folder))->toBeTrue();
    });

    it('denies non-owner from deleting', function () {
        $otherUser = User::factory()->create();

        expect($this->policy->delete($otherUser, $this->folder))->toBeFalse();
    });
});

describe('restore', function () {
    it('always denies', function () {
        expect($this->policy->restore($this->user, $this->folder))->toBeFalse();
    });
});

describe('forceDelete', function () {
    it('always denies', function () {
        expect($this->policy->forceDelete($this->user, $this->folder))->toBeFalse();
    });
});
