<?php

namespace App\Http\Controllers\FileManager;

use App\Http\Controllers\Controller;
use App\Models\FileManager\FileEntry;
use App\Models\FileManager\Folder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', FileEntry::class);

        $data = $request->validate([
            'folder_id' => ['nullable', 'exists:folders,id'],
            'title' => ['nullable', 'string', 'max:255'],
            'file' => ['required', 'file', 'max:10240'],
        ]);

        if (! empty($data['folder_id'])) {
            $folder = Folder::findOrFail($data['folder_id']);
            $this->authorize('update', $folder);
        }

        $file = $request->file('file');
        $user = $request->user();
        $path = $file->store("files/{$user->id}", 'public');

        $entry = FileEntry::create([
            'folder_id' => $data['folder_id'] ?? null,
            'owner_id' => $user->id,
            'title' => $data['title'] ?? null,
            'original_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'mime_type' => $file->getClientMimeType(),
            'size_bytes' => $file->getSize(),
        ]);

        return redirect()->route('files.index', ['folder' => $entry->folder_id])->with('status', 'file-uploaded');
    }

    public function show(FileEntry $file): View
    {
        $this->authorize('view', $file);

        $file->load(['owner', 'folder', 'versions.uploadedBy', 'comments.user', 'shares']);

        return view('files.show', ['file' => $file]);
    }

    public function update(Request $request, FileEntry $file): RedirectResponse
    {
        $this->authorize('update', $file);

        $data = $request->validate(['title' => ['nullable', 'string', 'max:255']]);
        $file->update($data);

        return redirect()->route('files.entries.show', $file)->with('status', 'file-renamed');
    }

    public function storeVersion(Request $request, FileEntry $file): RedirectResponse
    {
        $this->authorize('update', $file);

        $data = $request->validate(['file' => ['required', 'file', 'max:10240']]);

        $file->versions()->create([
            'original_name' => $file->original_name,
            'file_path' => $file->file_path,
            'mime_type' => $file->mime_type,
            'size_bytes' => $file->size_bytes,
            'uploaded_by_id' => $file->owner_id,
        ]);

        $uploaded = $data['file'];
        $path = $uploaded->store("files/{$file->owner_id}", 'public');

        $file->update([
            'original_name' => $uploaded->getClientOriginalName(),
            'file_path' => $path,
            'mime_type' => $uploaded->getClientMimeType(),
            'size_bytes' => $uploaded->getSize(),
        ]);

        return redirect()->route('files.entries.show', $file)->with('status', 'file-version-added');
    }

    public function destroy(FileEntry $file): RedirectResponse
    {
        $this->authorize('delete', $file);

        $folderId = $file->folder_id;
        $file->delete();

        return redirect()->route('files.index', ['folder' => $folderId])->with('status', 'file-deleted');
    }

    public function restore(int $id): RedirectResponse
    {
        $file = FileEntry::onlyTrashed()->findOrFail($id);
        $this->authorize('update', $file);

        $file->restore();

        return redirect()->route('files.trash')->with('status', 'file-restored');
    }

    public function move(Request $request, FileEntry $file): RedirectResponse
    {
        $this->authorize('update', $file);

        $data = $request->validate(['folder_id' => ['nullable', 'exists:folders,id']]);
        $destinationId = $data['folder_id'] ?? null;

        if ($destinationId) {
            $destination = Folder::findOrFail($destinationId);
            $this->authorize('update', $destination);
        }

        $file->update(['folder_id' => $destinationId]);

        return redirect()->route('files.index', ['folder' => $destinationId])->with('status', 'file-moved');
    }

    public function download(FileEntry $file): StreamedResponse
    {
        $this->authorize('view', $file);

        return Storage::disk('public')->download($file->file_path, $file->original_name);
    }

    public function downloadByToken(string $token): StreamedResponse
    {
        $file = FileEntry::where('share_token', $token)->firstOrFail();

        abort_if(
            $file->share_token_expires_at && $file->share_token_expires_at->isPast(),
            404
        );

        return Storage::disk('public')->download($file->file_path, $file->original_name);
    }

    public function enableShareLink(Request $request, FileEntry $file): RedirectResponse
    {
        $this->authorize('update', $file);

        $data = $request->validate(['expires_at' => ['nullable', 'date', 'after:now']]);

        $file->update([
            'share_token' => Str::random(40),
            'share_token_expires_at' => $data['expires_at'] ?? null,
        ]);

        return redirect()->route('files.entries.show', $file)->with('status', 'file-share-link-enabled');
    }

    public function disableShareLink(FileEntry $file): RedirectResponse
    {
        $this->authorize('update', $file);

        $file->update(['share_token' => null, 'share_token_expires_at' => null]);

        return redirect()->route('files.entries.show', $file)->with('status', 'file-share-link-disabled');
    }
}
