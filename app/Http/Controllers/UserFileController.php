<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class UserFileController extends Controller
{
    public function index(Request $request): \Inertia\Response|\Illuminate\Http\RedirectResponse
    {
        $user = $request->user();
        $folderId = $request->input('folder_id');

        $folders = $user->mediaFolders()->orderBy('name')->get();

        // ✅ Cek folder aktif
        $currentFolder = $folderId ? $user->mediaFolders()->find($folderId) : null;

        if ($folderId && ! $currentFolder) {
            // 🛑 Jika folder tidak ada, redirect ke root
            return redirect('/files');
        }

        $files = $user
            ->media()
            ->where('collection_name', 'files')
            ->when($folderId, function ($query) use ($folderId) {
                $query->whereIn('custom_properties->folder_id', [(string) $folderId, (int) $folderId]);
            }, function ($query) {
                $query->whereNull('custom_properties->folder_id');
            })
            ->get();

        return Inertia::render('files/Index', [
            'folders' => $folders,
            'currentFolderId' => $folderId,
            'currentFolder' => $currentFolder,
            'files' => $files->map(fn ($media) => [
                'id' => $media->id,
                'name' => $media->name,
                'size' => $media->humanReadableSize,
                'mime_type' => $media->mime_type,
                'url' => $media->getFullUrl(),
                'created_at' => $media->created_at->diffForHumans(),
            ]),
        ]);
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'files' => 'required|array',
            'files.*' => 'file|max:10240',
            'folder_id' => ['nullable', 'integer', Rule::exists('media_folders', 'id')->where('user_id', $request->user()->id)],
        ]);

        foreach ($request->file('files') as $file) {
            $request->user()
                ->addMedia($file)
                ->withCustomProperties([
                    'folder_id' => $request->filled('folder_id') ? (string) $request->input('folder_id') : null,
                ])
                ->toMediaCollection('files');
        }

        return back()->with('success', 'Files uploaded successfully');
    }

    public function destroy(Request $request, int $id): \Illuminate\Http\RedirectResponse
    {
        $media = $request->user()->media()->where('id', $id)->firstOrFail();
        $media->delete();

        return back()->with('success', 'File berhasil dihapus.');
    }
}
