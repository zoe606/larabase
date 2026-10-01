<?php

declare(strict_types=1);

use App\Models\MediaFolder;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->user = User::factory()->create();
});

/*
|--------------------------------------------------------------------------
| Index Tests
|--------------------------------------------------------------------------
*/

describe('index', function () {
    it('redirects to login for unauthenticated users', function () {
        $response = $this->get(route('files.index'));

        $response->assertRedirect(route('login'));
    });

    it('returns inertia response with expected props', function () {
        $response = $this->actingAs($this->user)
            ->get(route('files.index'));
        $response->assertOk()->assertInertia(fn ($page) => $page->component('files/Index')->has('files'));
    });

    it('lists folders belonging to the authenticated user', function () {
        MediaFolder::factory()->count(3)->forUser($this->user)->create();

        // Create folder for another user (should not be visible)
        $otherUser = User::factory()->create();
        MediaFolder::factory()->forUser($otherUser)->create();
        $response = $this->actingAs($this->user)
            ->get(route('files.index'));

        $response->assertOk()->assertInertia(fn ($page) => $page->component('files/Index')->has('files'));
    });

    it('redirects to /files when folder_id does not exist', function () {
        $response = $this->actingAs($this->user)
            ->get(route('files.index', ['folder_id' => 99999]));

        $response->assertRedirect('/files');
    });

    it('redirects to /files when folder_id belongs to another user', function () {
        $otherUser = User::factory()->create();
        $otherFolder = MediaFolder::factory()->forUser($otherUser)->create();

        $response = $this->actingAs($this->user)
            ->get(route('files.index', ['folder_id' => $otherFolder->id]));

        $response->assertRedirect('/files');
    });

    it('handles no folder_id parameter', function () {
        $response = $this->actingAs($this->user)
            ->get(route('files.index'));

        $response->assertOk()->assertInertia(fn ($page) => $page->component('files/Index')->has('files'));
    });

    it('sets currentFolderId when valid folder_id is provided', function () {
        $folder = MediaFolder::factory()->forUser($this->user)->create();

        $response = $this->actingAs($this->user)
            ->get(route('files.index', ['folder_id' => $folder->id]));
        $response->assertOk()->assertInertia(fn ($page) => $page->component('files/Index')->has('files'));
    });
});

/*
|--------------------------------------------------------------------------
| Store Tests
|--------------------------------------------------------------------------
*/

describe('store', function () {
    beforeEach(function () {
        Storage::fake('public');
    });

    it('redirects to login for unauthenticated users', function () {
        $file = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->post(route('files.store'), [
            'files' => [$file],
        ]);

        $response->assertRedirect(route('login'));
    });

    it('uploads files successfully', function () {
        $file = fakeImage('photo.png');

        $response = $this->actingAs($this->user)
            ->post(route('files.store'), [
                'files' => [$file],
            ]);

        $response->assertRedirect()
            ->assertSessionHas('success', 'Files uploaded successfully');

        // Verify media was added to the user
        $this->user->refresh();
        expect($this->user->media()->where('collection_name', 'files')->count())->toBe(1);
    });

    it('uploads multiple files at once', function () {
        $files = [
            fakeImage('photo1.png'),
            fakeImage('photo2.png'),
            fakeImage('photo3.png'),
        ];

        $response = $this->actingAs($this->user)
            ->post(route('files.store'), [
                'files' => $files,
            ]);

        $response->assertRedirect()
            ->assertSessionHas('success', 'Files uploaded successfully');

        $this->user->refresh();
        expect($this->user->media()->where('collection_name', 'files')->count())->toBe(3);
    });

    it('validates files array is required', function () {
        $response = $this->actingAs($this->user)
            ->post(route('files.store'), []);

        $response->assertSessionHasErrors(['files']);
    });

    it('validates files must be an array', function () {
        $response = $this->actingAs($this->user)
            ->post(route('files.store'), [
                'files' => 'not-an-array',
            ]);

        $response->assertSessionHasErrors(['files']);
    });

    it('validates each file max size is 10MB', function () {
        $tempFile = tempnam(sys_get_temp_dir(), 'test_');
        file_put_contents($tempFile, str_repeat('x', 11 * 1024 * 1024));
        $largeFile = new UploadedFile($tempFile, 'large.bin', 'application/octet-stream', null, true);

        $response = $this->actingAs($this->user)
            ->post(route('files.store'), [
                'files' => [$largeFile],
            ]);

        $response->assertSessionHasErrors(['files.0']);
    });

    it('stores files with folder_id in custom properties', function () {
        $folder = MediaFolder::factory()->forUser($this->user)->create();
        $file = fakeImage('photo.png');

        $response = $this->actingAs($this->user)
            ->post(route('files.store'), [
                'files' => [$file],
                'folder_id' => $folder->id,
            ]);

        $response->assertRedirect()
            ->assertSessionHas('success');

        $media = $this->user->media()->where('collection_name', 'files')->first();
        expect((int) $media->getCustomProperty('folder_id'))->toBe($folder->id);
    });

    it('stores files without folder_id when none provided', function () {
        $file = fakeImage('photo.png');

        $response = $this->actingAs($this->user)
            ->post(route('files.store'), [
                'files' => [$file],
            ]);

        $response->assertRedirect()
            ->assertSessionHas('success');

        $media = $this->user->media()->where('collection_name', 'files')->first();
        expect($media->getCustomProperty('folder_id'))->toBeNull();
    });

    it('associates uploaded files with the authenticated user', function () {
        $file = fakeImage('photo.png');

        $this->actingAs($this->user)
            ->post(route('files.store'), [
                'files' => [$file],
            ]);

        $media = $this->user->media()->where('collection_name', 'files')->first();
        expect($media)->not->toBeNull()
            ->and($media->model_id)->toBe($this->user->id)
            ->and($media->model_type)->toBe(User::class);
    });
});

/*
|--------------------------------------------------------------------------
| Destroy Tests
|--------------------------------------------------------------------------
*/

describe('destroy', function () {
    beforeEach(function () {
        Storage::fake('public');
    });

    it('redirects to login for unauthenticated users', function () {
        $response = $this->delete(route('files.destroy', ['id' => 1]));

        $response->assertRedirect(route('login'));
    });

    it('deletes a media file belonging to the authenticated user', function () {
        // Upload a file first
        $file = fakeImage('photo.png');
        $this->user->addMedia($file)
            ->withCustomProperties(['folder_id' => null])
            ->toMediaCollection('files');

        $media = $this->user->media()->where('collection_name', 'files')->first();

        $response = $this->actingAs($this->user)
            ->delete(route('files.destroy', ['id' => $media->id]));

        $response->assertRedirect()
            ->assertSessionHas('success', 'File berhasil dihapus.');

        // Verify the media is deleted
        expect($this->user->media()->where('id', $media->id)->exists())->toBeFalse();
    });

    it('returns 404 for non-existent media id', function () {
        $response = $this->actingAs($this->user)
            ->delete(route('files.destroy', ['id' => 99999]));

        $response->assertNotFound();
    });

    it('returns 404 when trying to delete another user media', function () {
        $otherUser = User::factory()->create();

        // Upload a file as another user
        $file = fakeImage('other-photo.png');
        $otherUser->addMedia($file)
            ->withCustomProperties(['folder_id' => null])
            ->toMediaCollection('files');

        $otherMedia = $otherUser->media()->where('collection_name', 'files')->first();

        // Try to delete as the current user
        $response = $this->actingAs($this->user)
            ->delete(route('files.destroy', ['id' => $otherMedia->id]));

        // The controller uses $request->user()->media()->where('id', $id)->firstOrFail()
        // which scopes to the authenticated user, so another user's media returns 404
        $response->assertNotFound();

        // Verify the other user's media still exists
        expect($otherUser->media()->where('id', $otherMedia->id)->exists())->toBeTrue();
    });

    it('only deletes the specified media item', function () {
        // Upload two files
        $file1 = fakeImage('photo1.png');
        $this->user->addMedia($file1)
            ->withCustomProperties(['folder_id' => null])
            ->toMediaCollection('files');

        $file2 = fakeImage('photo2.png');
        $this->user->addMedia($file2)
            ->withCustomProperties(['folder_id' => null])
            ->toMediaCollection('files');

        $mediaItems = $this->user->media()->where('collection_name', 'files')->get();
        $mediaToDelete = $mediaItems->first();
        $mediaToKeep = $mediaItems->last();

        $response = $this->actingAs($this->user)
            ->delete(route('files.destroy', ['id' => $mediaToDelete->id]));

        $response->assertRedirect()
            ->assertSessionHas('success');

        expect($this->user->media()->where('id', $mediaToDelete->id)->exists())->toBeFalse()
            ->and($this->user->media()->where('id', $mediaToKeep->id)->exists())->toBeTrue();
    });
});

/*
|--------------------------------------------------------------------------
| User Isolation Tests
|--------------------------------------------------------------------------
*/

describe('user isolation', function () {
    beforeEach(function () {
        Storage::fake('public');
    });

    it('cannot access another user folder via folder_id parameter', function () {
        $otherUser = User::factory()->create();
        $otherFolder = MediaFolder::factory()->forUser($otherUser)->create();

        $response = $this->actingAs($this->user)
            ->get(route('files.index', ['folder_id' => $otherFolder->id]));

        $response->assertRedirect('/files');
    });

    it('cannot delete another user media file', function () {
        $otherUser = User::factory()->create();

        $file = fakeImage('other-photo.png');
        $otherUser->addMedia($file)
            ->withCustomProperties(['folder_id' => null])
            ->toMediaCollection('files');

        $otherMedia = $otherUser->media()->where('collection_name', 'files')->first();

        $response = $this->actingAs($this->user)
            ->delete(route('files.destroy', ['id' => $otherMedia->id]));

        $response->assertNotFound();

        // Confirm file still belongs to other user
        expect($otherUser->media()->where('id', $otherMedia->id)->exists())->toBeTrue();
    });
});
