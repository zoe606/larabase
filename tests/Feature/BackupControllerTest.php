<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    $this->backupName = 'test-backup-'.bin2hex(random_bytes(8));
    config(['backup.backup.name' => $this->backupName]);
    $this->backupDirectory = storage_path('app/private/'.$this->backupName);
    File::ensureDirectoryExists($this->backupDirectory);
    $this->actingAs(User::factory()->create());
});

afterEach(function () {
    File::deleteDirectory($this->backupDirectory);
});

it('lists only backup archives from the configured directory', function () {
    File::put($this->backupDirectory.'/snapshot.zip', 'archive');
    File::put($this->backupDirectory.'/notes.txt', 'notes');
    $this->get(route('backup.index'))->assertOk()->assertInertia(fn ($page) => $page
        ->component('backup/Index')->has('backups', 1)->where('backups.0.name', 'snapshot.zip'));
});

it('downloads and deletes an archive from the configured directory', function () {
    File::put($this->backupDirectory.'/snapshot.zip', 'archive');
    $this->get(route('backup.download', 'snapshot.zip'))->assertDownload('snapshot.zip');
    $this->delete(route('backup.delete', 'snapshot.zip'))->assertRedirect()->assertSessionHas('success');
    expect(File::exists($this->backupDirectory.'/snapshot.zip'))->toBeFalse();
});

it('handles missing backup files', function () {
    $this->get(route('backup.download', 'missing.zip'))->assertNotFound();
    $this->delete(route('backup.delete', 'missing.zip'))->assertRedirect()->assertSessionHas('error');
});
