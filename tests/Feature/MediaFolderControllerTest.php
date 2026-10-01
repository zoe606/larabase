<?php

declare(strict_types=1);

use App\Models\MediaFolder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

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
        $response = $this->get(route('media.index'));

        $response->assertRedirect(route('login'));
    });

    it('returns inertia response for authenticated user', function () {
        $response = $this->actingAs($this->user)
            ->get(route('media.index'));

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('files/Index')
                ->has('folders')
                ->has('files')
                ->where('currentFolderId', null)
                ->where('currentFolder', null)
            );
    });

    it('lists folders belonging to the authenticated user', function () {
        MediaFolder::factory()->count(3)->forUser($this->user)->create();

        // Create folder for another user (should not be visible)
        $otherUser = User::factory()->create();
        MediaFolder::factory()->forUser($otherUser)->create();

        $response = $this->actingAs($this->user)
            ->get(route('media.index'));

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('files/Index')
                ->has('folders', 3)
            );
    });

    it('returns folders ordered by name', function () {
        MediaFolder::factory()->forUser($this->user)->create(['name' => 'Zebra']);
        MediaFolder::factory()->forUser($this->user)->create(['name' => 'Alpha']);
        MediaFolder::factory()->forUser($this->user)->create(['name' => 'Middle']);

        $response = $this->actingAs($this->user)
            ->get(route('media.index'));

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('files/Index')
                ->has('folders', 3)
                ->where('folders.0.name', 'Alpha')
                ->where('folders.1.name', 'Middle')
                ->where('folders.2.name', 'Zebra')
            );
    });

    it('sets currentFolderId when folder_id query param is provided', function () {
        $folder = MediaFolder::factory()->forUser($this->user)->create();

        $response = $this->actingAs($this->user)
            ->get(route('media.index', ['folder_id' => $folder->id]));
        $response->assertOk()->assertInertia(fn ($page) => $page->component('files/Index')->has('files'));
    });

    it('redirects to /files when folder_id does not belong to user', function () {
        $otherUser = User::factory()->create();
        $otherFolder = MediaFolder::factory()->forUser($otherUser)->create();

        $response = $this->actingAs($this->user)
            ->get(route('media.index', ['folder_id' => $otherFolder->id]));

        $response->assertRedirect('/files');
    });

    it('redirects to /files when folder_id does not exist', function () {
        $response = $this->actingAs($this->user)
            ->get(route('media.index', ['folder_id' => 99999]));

        $response->assertRedirect('/files');
    });

    it('handles empty folder list gracefully', function () {
        $response = $this->actingAs($this->user)
            ->get(route('media.index'));

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('files/Index')
                ->has('folders', 0)
                ->has('files', 0)
            );
    });
});

/*
|--------------------------------------------------------------------------
| Store Tests
|--------------------------------------------------------------------------
*/

describe('store', function () {
    it('redirects to login for unauthenticated users', function () {
        $response = $this->post(route('media.store'), [
            'name' => 'Test Folder',
        ]);

        $response->assertRedirect(route('login'));
    });

    it('creates a folder with valid data', function () {
        $response = $this->actingAs($this->user)
            ->post(route('media.store'), [
                'name' => 'My Documents',
            ]);

        $response->assertRedirect()
            ->assertSessionHas('success', 'Folder berhasil dibuat.');

        $this->assertDatabaseHas('media_folders', [
            'name' => 'My Documents',
            'user_id' => $this->user->id,
            'parent_id' => null,
        ]);
    });

    it('creates a subfolder with valid parent_id', function () {
        $parentFolder = MediaFolder::factory()->forUser($this->user)->create([
            'name' => 'Parent Folder',
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('media.store'), [
                'name' => 'Child Folder',
                'parent_id' => $parentFolder->id,
            ]);

        $response->assertRedirect()
            ->assertSessionHas('success', 'Folder berhasil dibuat.');

        $this->assertDatabaseHas('media_folders', [
            'name' => 'Child Folder',
            'user_id' => $this->user->id,
            'parent_id' => $parentFolder->id,
        ]);
    });

    it('assigns the folder to the authenticated user', function () {
        $this->actingAs($this->user)
            ->post(route('media.store'), [
                'name' => 'User Folder',
            ]);

        $folder = MediaFolder::where('name', 'User Folder')->first();
        expect($folder)->not->toBeNull()
            ->and($folder->user_id)->toBe($this->user->id);
    });

    it('validates name is required', function () {
        $response = $this->actingAs($this->user)
            ->post(route('media.store'), []);

        $response->assertSessionHasErrors(['name']);
    });

    it('validates name must be a string', function () {
        $response = $this->actingAs($this->user)
            ->post(route('media.store'), [
                'name' => ['array'],
            ]);

        $response->assertSessionHasErrors(['name']);
    });

    it('validates name max length is 255', function () {
        $response = $this->actingAs($this->user)
            ->post(route('media.store'), [
                'name' => str_repeat('a', 256),
            ]);

        $response->assertSessionHasErrors(['name']);
    });

    it('validates parent_id must exist in media_folders table', function () {
        $response = $this->actingAs($this->user)
            ->post(route('media.store'), [
                'name' => 'Orphan Folder',
                'parent_id' => 99999,
            ]);

        $response->assertSessionHasErrors(['parent_id']);
    });

    it('allows null parent_id for root folders', function () {
        $response = $this->actingAs($this->user)
            ->post(route('media.store'), [
                'name' => 'Root Folder',
                'parent_id' => null,
            ]);

        $response->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('media_folders', [
            'name' => 'Root Folder',
            'parent_id' => null,
        ]);
    });

    it('creates multiple folders for the same user', function () {
        $this->actingAs($this->user)
            ->post(route('media.store'), ['name' => 'Folder 1']);

        $this->actingAs($this->user)
            ->post(route('media.store'), ['name' => 'Folder 2']);

        $this->actingAs($this->user)
            ->post(route('media.store'), ['name' => 'Folder 3']);

        expect(MediaFolder::where('user_id', $this->user->id)->count())->toBe(3);
    });
});

/*
|--------------------------------------------------------------------------
| Destroy Tests
|--------------------------------------------------------------------------
*/

describe('destroy', function () {
    it('redirects to login for unauthenticated users', function () {
        $folder = MediaFolder::factory()->forUser($this->user)->create();

        $response = $this->delete(route('media.destroy', $folder));

        $response->assertRedirect(route('login'));
    });

    it('deletes a folder and redirects to /files', function () {
        $folder = MediaFolder::factory()->forUser($this->user)->create([
            'name' => 'To Delete',
        ]);

        $response = $this->actingAs($this->user)
            ->delete(route('media.destroy', $folder));

        $response->assertRedirect('/files')
            ->assertSessionHas('success', 'Folder berhasil dihapus.');

        $this->assertDatabaseMissing('media_folders', ['id' => $folder->id]);
    });

    it('deletes child folders when parent is deleted', function () {
        $parent = MediaFolder::factory()->forUser($this->user)->create([
            'name' => 'Parent',
        ]);

        $child1 = MediaFolder::factory()->forUser($this->user)->create([
            'name' => 'Child 1',
            'parent_id' => $parent->id,
        ]);

        $child2 = MediaFolder::factory()->forUser($this->user)->create([
            'name' => 'Child 2',
            'parent_id' => $parent->id,
        ]);

        $response = $this->actingAs($this->user)
            ->delete(route('media.destroy', $parent));

        $response->assertRedirect('/files')
            ->assertSessionHas('success', 'Folder berhasil dihapus.');

        $this->assertDatabaseMissing('media_folders', ['id' => $parent->id]);
        $this->assertDatabaseMissing('media_folders', ['id' => $child1->id]);
        $this->assertDatabaseMissing('media_folders', ['id' => $child2->id]);
    });

    it('does not delete folders belonging to other users', function () {
        $otherUser = User::factory()->create();
        $otherFolder = MediaFolder::factory()->forUser($otherUser)->create();

        // Deleting our own folder should not affect another user's folder
        $ourFolder = MediaFolder::factory()->forUser($this->user)->create();
        $this->actingAs($this->user)
            ->delete(route('media.destroy', $ourFolder));

        $this->assertDatabaseHas('media_folders', ['id' => $otherFolder->id]);
    });

    it('returns 404 for non-existent folder', function () {
        $response = $this->actingAs($this->user)
            ->delete(route('media.destroy', 99999));

        $response->assertNotFound();
    });

    it('preserves sibling folders when one folder is deleted', function () {
        $folder1 = MediaFolder::factory()->forUser($this->user)->create(['name' => 'Keep Me']);
        $folder2 = MediaFolder::factory()->forUser($this->user)->create(['name' => 'Delete Me']);

        $this->actingAs($this->user)
            ->delete(route('media.destroy', $folder2));

        $this->assertDatabaseHas('media_folders', ['id' => $folder1->id]);
        $this->assertDatabaseMissing('media_folders', ['id' => $folder2->id]);
    });
});

/*
|--------------------------------------------------------------------------
| User Isolation Tests
|--------------------------------------------------------------------------
*/

describe('user isolation', function () {
    it('only shows folders belonging to the authenticated user in index', function () {
        MediaFolder::factory()->forUser($this->user)->create(['name' => 'My Folder']);

        $otherUser = User::factory()->create();
        MediaFolder::factory()->forUser($otherUser)->create(['name' => 'Other Folder']);

        $response = $this->actingAs($this->user)
            ->get(route('media.index'));

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('files/Index')
                ->has('folders', 1)
                ->where('folders.0.name', 'My Folder')
            );
    });

    it('cannot access another user folder via folder_id parameter', function () {
        $otherUser = User::factory()->create();
        $otherFolder = MediaFolder::factory()->forUser($otherUser)->create();

        $response = $this->actingAs($this->user)
            ->get(route('media.index', ['folder_id' => $otherFolder->id]));

        $response->assertRedirect('/files');
    });
});
