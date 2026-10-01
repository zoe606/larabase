<?php

declare(strict_types=1);

use App\Models\MediaFolder;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
});

it('lists uploaded files in their folder and keeps root files separate', function (string $route) {
    $user = User::factory()->create();
    $folder = MediaFolder::factory()->forUser($user)->create();

    $this->actingAs($user)->post(route('files.store'), [
        'files' => [UploadedFile::fake()->create('folder.txt', 1)],
        'folder_id' => $folder->id,
    ])->assertRedirect()->assertSessionHasNoErrors();
    $folderFile = $user->media()->firstOrFail();
    $rootFile = $user->addMedia(UploadedFile::fake()->create('root.txt', 1))->toMediaCollection('files');

    $this->get(route($route, ['folder_id' => $folder->id]))
        ->assertOk()->assertInertia(fn ($page) => $page
        ->has('files', 1)->where('files.0.id', $folderFile->id));
    $this->get(route($route))->assertOk()->assertInertia(fn ($page) => $page
        ->has('files', 1)->where('files.0.id', $rootFile->id));
})->with(['files.index', 'media.index']);

it('deletes files stored with numeric or string folder identifiers', function () {
    $user = User::factory()->create();
    $folder = MediaFolder::factory()->forUser($user)->create();
    $ids = [];
    foreach ([$folder->id, (string) $folder->id] as $folderId) {
        $ids[] = $user->addMedia(UploadedFile::fake()->create('folder.txt', 1))
            ->withCustomProperties(['folder_id' => $folderId])->toMediaCollection('files')->id;
    }
    $rootFile = $user->addMedia(UploadedFile::fake()->create('root.txt', 1))->toMediaCollection('files');

    $this->actingAs($user)->delete(route('media.destroy', $folder))->assertRedirect('/files');
    foreach ($ids as $id) {
        $this->assertDatabaseMissing('media', ['id' => $id]);
    }
    $this->assertDatabaseHas('media', ['id' => $rootFile->id]);
});

it('rejects deleting another users folder', function () {
    $owner = User::factory()->create();
    $folder = MediaFolder::factory()->forUser($owner)->create();

    $this->actingAs(User::factory()->create())->delete(route('media.destroy', $folder))->assertForbidden();
    $this->assertDatabaseHas('media_folders', ['id' => $folder->id]);
});

it('renames an owned folder and rejects another users folder', function () {
    $user = User::factory()->create();
    $folder = MediaFolder::factory()->forUser($user)->create();

    $this->actingAs($user)->put(route('media.update', $folder), ['name' => 'Renamed'])
        ->assertRedirect()->assertSessionHasNoErrors();
    expect($folder->fresh()->name)->toBe('Renamed');
    $this->actingAs(User::factory()->create())->put(route('media.update', $folder), ['name' => 'Other'])
        ->assertForbidden();
    expect($folder->fresh()->name)->toBe('Renamed');
});
