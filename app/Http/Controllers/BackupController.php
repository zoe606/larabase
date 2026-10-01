<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Inertia\Inertia;

class BackupController extends Controller
{
    private function backupPath(): string
    {
        return 'private/'.config('backup.backup.name', config('app.name'));
    }

    public function index(): \Inertia\Response
    {
        $realPath = storage_path('app/'.$this->backupPath());

        $files = File::exists($realPath) ? File::files($realPath) : [];

        $backups = collect($files)
            ->filter(fn ($file) => $file->getExtension() === 'zip')
            ->map(fn ($file) => [
                'name' => $file->getFilename(),
                'size' => $file->getSize(),
                'last_modified' => $file->getMTime(),
                'download_url' => route('backup.download', ['file' => $file->getFilename()]),
            ])
            ->sortByDesc('last_modified')
            ->values();

        return Inertia::render('backup/Index', [
            'backups' => $backups,
        ]);
    }

    public function run(): \Illuminate\Http\RedirectResponse
    {
        Artisan::call('backup:run --only-db');

        return redirect()->back()->with('success', 'Backup berhasil dibuat.');
    }

    public function download(string $file): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $path = storage_path('app/'.$this->backupPath().'/'.$file);

        if (! file_exists($path)) {
            abort(404, 'File tidak ditemukan.');
        }

        return response()->download($path);
    }

    public function delete(string $file): \Illuminate\Http\RedirectResponse
    {
        $path = storage_path('app/'.$this->backupPath().'/'.$file);

        if (! file_exists($path)) {
            return redirect()->back()->with('error', 'File tidak ditemukan.');
        }

        unlink($path);

        return redirect()->back()->with('success', 'Backup berhasil dihapus.');
    }
}
