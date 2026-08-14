<?php

namespace App\Services\FileManager;

use App\Models\FileManager\FileEntry;
use App\Models\FileManager\Folder;
use Illuminate\Support\Facades\DB;

class FolderService
{
    public function delete(Folder $folder): void
    {
        DB::transaction(function () use ($folder) {
            foreach ($folder->children as $child) {
                $this->delete($child);
            }

            FileEntry::where('folder_id', $folder->id)->get()->each->delete();

            $folder->delete();
        });
    }

    public function restore(Folder $folder): void
    {
        DB::transaction(function () use ($folder) {
            $folder->restore();

            FileEntry::onlyTrashed()->where('folder_id', $folder->id)->get()->each->restore();

            foreach (Folder::onlyTrashed()->where('parent_id', $folder->id)->get() as $child) {
                $this->restore($child);
            }
        });
    }
}
