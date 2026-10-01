<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\MediaFolder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class MediaFolderController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): \Inertia\Response|\Illuminate\Http\RedirectResponse
    {
        $user = $request->user();
        $folderId = $request->input('folder_id');

        // Ambil semua folder
        $folders = $user->mediaFolders()->orderBy('name')->get();

        // Pastikan folder valid
        $currentFolder = null;
        if ($folderId) {
            $currentFolder = $user->mediaFolders()->find($folderId);
            if (! $currentFolder) {
                return redirect('/files');
            }
        }

        // Ambil file sesuai folder
        $files = $user->media()
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
            'currentFolder' => $currentFolder, // wajib!
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
            'name' => 'required|string|max:255',
            'parent_id' => ['nullable', Rule::exists('media_folders', 'id')->where('user_id', $request->user()->id)],
        ]);

        $request->user()->mediaFolders()->create([
            'name' => $request->name,
            'parent_id' => $request->parent_id,
        ]);

        return back()->with('success', 'Folder berhasil dibuat.');
    }

    public function update(Request $request, MediaFolder $medium): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('update', $medium);
        $medium->update($request->validate(['name' => 'required|string|max:255']));

        return back()->with('success', 'Folder renamed successfully.');
    }

    public function destroy(MediaFolder $medium): \Illuminate\Http\RedirectResponse
    {
        $folder = $medium;
        $this->authorize('delete', $folder);
        $user = $folder->user;

        // 🔁 Hapus semua file dalam folder ini
        $files = $user->media()
            ->where('collection_name', 'files')
            ->whereIn('custom_properties->folder_id', [(string) $folder->id, $folder->id])
            ->get();

        foreach ($files as $file) {
            $file->delete();
        }

        // 🔁 Hapus subfolder langsung (1 level)
        $childFolders = $user->mediaFolders()->where('parent_id', $folder->id)->get();
        foreach ($childFolders as $child) {
            $child->delete();
        }

        // 🗑️ Hapus folder utama
        $folder->delete();

        return redirect('/files')->with('success', 'Folder berhasil dihapus.');
    }
}
