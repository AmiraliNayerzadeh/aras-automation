<?php

namespace App\Services\Merchandising;

use App\Models\FileManager\FileEntry;
use App\Models\FileManager\FileShare;
use App\Models\FileManager\Folder;
use App\Models\Merchandising\Upload;
use App\Models\Merchandising\Visit;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

/**
 * Files a visit's photos and videos into the file manager, without the
 * merchandiser having to think about it:
 *
 *   Merchandising / 2026-11 / <store> / 2026-11-18 14:30 - <merchandiser>
 *
 * Every folder and file is shared with the roles that review merchandising,
 * which also keeps them out of the company-wide "open to everyone" default.
 * The merchandiser owns their own visit folder and files.
 */
class VisitFiler
{
    public const ROOT_FOLDER = 'Merchandising';

    /** @var list<int>|null */
    private ?array $reviewerRoleIds = null;

    /**
     * Copies each upload into public storage and creates its file entry.
     * Returns the stored paths so a caller can remove them if its own
     * transaction rolls back.
     *
     * @param  iterable<Upload>  $uploads
     * @return list<string>
     */
    public function file(Visit $visit, iterable $uploads): array
    {
        $user = $visit->user;
        $folder = $this->visitFolder($visit);
        $visit->forceFill(['folder_id' => $folder->id])->save();

        $stored = [];
        $counts = [];

        foreach ($uploads as $upload) {
            $extension = strtolower(pathinfo($upload->original_name, PATHINFO_EXTENSION)) ?: $this->extensionFor($upload->mime_type);
            $path = "files/{$user->id}/merchandising/".Str::uuid().($extension ? ".{$extension}" : '');

            $stream = Storage::disk('local')->readStream($upload->temp_path);
            Storage::disk('public')->writeStream($path, $stream);
            is_resource($stream) && fclose($stream);
            $stored[] = $path;

            $counts[$upload->kind] = ($counts[$upload->kind] ?? 0) + 1;

            $entry = FileEntry::create([
                'folder_id' => $folder->id,
                'owner_id' => $user->id,
                'title' => ucfirst($upload->kind).' '.$counts[$upload->kind],
                'original_name' => $upload->original_name,
                'file_path' => $path,
                'mime_type' => $upload->mime_type,
                'size_bytes' => $upload->size_bytes,
            ]);
            $this->restrict($entry, 'file', $user);

            $upload->forceFill(['visit_id' => $visit->id, 'file_entry_id' => $entry->id])->save();
        }

        return $stored;
    }

    private function visitFolder(Visit $visit): Folder
    {
        $owner = $this->sharedOwner($visit->user);
        $root = $this->folder(self::ROOT_FOLDER, null, $owner);
        $month = $this->folder($visit->visited_at->format('Y-m'), $root, $owner);
        $store = $this->folder(Str::limit($visit->store?->name ?? 'Unlisted store', 120, ''), $month, $owner);

        return $this->folder($visit->visited_at->format('Y-m-d H:i').' - '.$visit->user->name, $store, $visit->user, unique: true);
    }

    private function folder(string $name, ?Folder $parent, User $owner, bool $unique = false): Folder
    {
        $name = trim(str_replace(['/', '\\'], '-', $name));

        if (! $unique) {
            $existing = Folder::where('parent_id', $parent?->id)->where('name', $name)->first();

            if ($existing) {
                return $existing;
            }
        } else {
            // Two visits to one store in the same minute get separate folders.
            $base = $name;

            for ($n = 2; Folder::where('parent_id', $parent?->id)->where('name', $name)->exists(); $n++) {
                $name = "{$base} ({$n})";
            }
        }

        $folder = Folder::create(['name' => $name, 'parent_id' => $parent?->id, 'owner_id' => $owner->id]);
        $this->restrict($folder, 'folder', $owner);

        return $folder;
    }

    private function restrict(Folder|FileEntry $item, string $type, User $creator): void
    {
        foreach ($this->reviewerRoleIds() as $roleId) {
            FileShare::create([
                'shareable_type' => $type,
                'shareable_id' => $item->id,
                'grantee_type' => 'role',
                'grantee_id' => $roleId,
                'created_by_id' => $creator->id,
            ]);
        }
    }

    /**
     * @return list<int>
     */
    private function reviewerRoleIds(): array
    {
        return $this->reviewerRoleIds ??= Role::whereHas('permissions', fn ($q) => $q->where('name', 'merchandising.view_all'))
            ->pluck('id')->all();
    }

    /**
     * The shared folders belong to the senior administrator, so they don't
     * vanish with whichever merchandiser happened to file first.
     */
    private function sharedOwner(User $fallback): User
    {
        return User::role('super-admin')->orderBy('id')->first() ?? $fallback;
    }

    private function extensionFor(?string $mime): string
    {
        return match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/heic', 'image/heif' => 'heic',
            'video/mp4' => 'mp4',
            'video/quicktime' => 'mov',
            'video/webm' => 'webm',
            default => '',
        };
    }
}
