<?php

namespace App\Http\Controllers\FileManager;

use App\Http\Controllers\Controller;
use App\Models\FileManager\Folder;
use App\Services\FileManager\FolderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FolderController extends Controller
{
    public function __construct(protected FolderService $folders) {}

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Folder::class);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'exists:folders,id'],
        ]);

        if (! empty($data['parent_id'])) {
            $parent = Folder::findOrFail($data['parent_id']);
            $this->authorize('update', $parent);
        }

        $folder = Folder::create($data + ['owner_id' => $request->user()->id]);

        return redirect()->route('files.index', ['folder' => $folder->parent_id])->with('status', 'folder-created');
    }

    public function update(Request $request, Folder $folder): RedirectResponse
    {
        $this->authorize('update', $folder);

        $data = $request->validate(['name' => ['required', 'string', 'max:255']]);
        $folder->update($data);

        return redirect()->route('files.index', ['folder' => $folder->parent_id])->with('status', 'folder-renamed');
    }

    public function destroy(Folder $folder): RedirectResponse
    {
        $this->authorize('delete', $folder);

        $parentId = $folder->parent_id;
        $this->folders->delete($folder);

        return redirect()->route('files.index', ['folder' => $parentId])->with('status', 'folder-deleted');
    }

    public function restore(int $id): RedirectResponse
    {
        $folder = Folder::onlyTrashed()->findOrFail($id);
        $this->authorize('update', $folder);

        $this->folders->restore($folder);

        return redirect()->route('files.trash')->with('status', 'folder-restored');
    }

    public function move(Request $request, Folder $folder): RedirectResponse
    {
        $this->authorize('update', $folder);

        $data = $request->validate(['parent_id' => ['nullable', 'exists:folders,id']]);
        $destinationId = $data['parent_id'] ?? null;

        if ($destinationId) {
            $destination = Folder::findOrFail($destinationId);
            $this->authorize('update', $destination);

            abort_if(
                $destination->id === $folder->id || $destination->ancestors()->contains('id', $folder->id),
                422,
                __('files.error_move_into_self')
            );
        }

        $folder->update(['parent_id' => $destinationId]);

        return redirect()->route('files.index', ['folder' => $destinationId])->with('status', 'folder-moved');
    }
}
