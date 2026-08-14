<?php

namespace App\Http\Controllers\FileManager;

use App\Http\Controllers\Controller;
use App\Models\FileManager\FileEntry;
use App\Models\FileManager\FileVersion;
use Illuminate\Http\RedirectResponse;

class FileVersionController extends Controller
{
    public function restore(FileEntry $file, FileVersion $version): RedirectResponse
    {
        $this->authorize('update', $file);
        abort_unless($version->file_entry_id === $file->id, 404);

        $file->versions()->create([
            'original_name' => $file->original_name,
            'file_path' => $file->file_path,
            'mime_type' => $file->mime_type,
            'size_bytes' => $file->size_bytes,
            'uploaded_by_id' => $file->owner_id,
        ]);

        $file->update([
            'original_name' => $version->original_name,
            'file_path' => $version->file_path,
            'mime_type' => $version->mime_type,
            'size_bytes' => $version->size_bytes,
        ]);

        $version->delete();

        return redirect()->route('files.entries.show', $file)->with('status', 'file-version-restored');
    }
}
