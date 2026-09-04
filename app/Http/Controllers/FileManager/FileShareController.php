<?php

namespace App\Http\Controllers\FileManager;

use App\Http\Controllers\Controller;
use App\Models\FileManager\FileEntry;
use App\Models\FileManager\FileShare;
use App\Models\FileManager\Folder;
use App\Models\Organization\Department;
use App\Models\Organization\Position;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class FileShareController extends Controller
{
    public function storeForFolder(Request $request, Folder $folder): RedirectResponse
    {
        $this->authorize('update', $folder);
        $this->grant($request, $folder);

        return redirect()->route('files.index', ['folder' => $folder->id])->with('status', 'share-added');
    }

    public function destroyForFolder(Request $request, Folder $folder, FileShare $share): RedirectResponse
    {
        $this->authorize('update', $folder);
        abort_unless($share->shareable_id === $folder->id && $share->shareable_type === 'folder', 404);

        $label = $share->label();
        $share->delete();

        activity('file_manager')->causedBy($request->user())->performedOn($folder)
            ->event('unshared')->withProperties(['grantee' => $label])->log('Folder unshared with '.$label);

        return redirect()->route('files.index', ['folder' => $folder->id])->with('status', 'share-removed');
    }

    public function storeForFile(Request $request, FileEntry $file): RedirectResponse
    {
        $this->authorize('update', $file);
        $this->grant($request, $file);

        return redirect()->route('files.entries.show', $file)->with('status', 'share-added');
    }

    public function destroyForFile(Request $request, FileEntry $file, FileShare $share): RedirectResponse
    {
        $this->authorize('update', $file);
        abort_unless($share->shareable_id === $file->id && $share->shareable_type === 'file', 404);

        $label = $share->label();
        $share->delete();

        activity('file_manager')->causedBy($request->user())->performedOn($file)
            ->event('unshared')->withProperties(['grantee' => $label])->log('File unshared with '.$label);

        return redirect()->route('files.entries.show', $file)->with('status', 'share-removed');
    }

    protected function grant(Request $request, Folder|FileEntry $shareable): void
    {
        $data = $request->validate([
            'grantee_type' => ['required', Rule::in(['user', 'role', 'everyone', 'department', 'position'])],
            'grantee_value' => ['nullable'],
            'grantee_value.*' => ['integer'],
        ]);

        $type = $data['grantee_type'];

        // "user" allows sharing with several people in one submit (multi-select);
        // the other grantee types each target a single group, so one id is enough.
        $granteeIds = $type === 'user'
            ? User::whereIn('id', (array) ($data['grantee_value'] ?? []))->pluck('id')
            : collect([$this->resolveSingleGrantee($type, $data['grantee_value'] ?? null)])->filter();

        if ($type !== 'everyone' && $granteeIds->isEmpty()) {
            throw ValidationException::withMessages(['grantee_value' => __('files.error_grantee_required')]);
        }

        if ($type === 'everyone') {
            $granteeIds = collect([null]);
        }

        $labels = [];

        foreach ($granteeIds as $granteeId) {
            $share = $shareable->shares()->firstOrCreate(
                ['grantee_type' => $type, 'grantee_id' => $granteeId],
                ['created_by_id' => $request->user()->id]
            );

            $labels[] = $share->label();
        }

        if ($labels) {
            $noun = $shareable instanceof Folder ? 'Folder' : 'File';

            activity('file_manager')->causedBy($request->user())->performedOn($shareable)
                ->event('shared')->withProperties(['grantees' => $labels])
                ->log("{$noun} shared with ".implode(', ', $labels));
        }
    }

    protected function resolveSingleGrantee(string $type, mixed $value): ?int
    {
        $id = is_array($value) ? ($value[0] ?? null) : $value;

        return match ($type) {
            'role' => Role::find($id)?->id,
            'department' => Department::find($id)?->id,
            'position' => Position::find($id)?->id,
            default => null,
        };
    }
}
