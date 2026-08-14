<?php

namespace App\Http\Controllers\FileManager;

use App\Http\Controllers\Controller;
use App\Models\FileManager\FileEntry;
use App\Models\FileManager\Folder;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class FileManagerController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Folder::class);

        $user = $request->user();
        $folderId = $request->integer('folder') ?: null;
        $tab = $request->string('tab', 'mine')->toString();

        $currentFolder = null;
        $breadcrumb = collect();

        if ($folderId) {
            $currentFolder = Folder::findOrFail($folderId);
            $this->authorize('view', $currentFolder);

            $folders = $currentFolder->children()->with('owner')->get();
            $files = $currentFolder->files()->with('owner')->get();
            $breadcrumb = $currentFolder->ancestors()->push($currentFolder);
        } elseif ($tab === 'shared') {
            // Lists every folder/file directly shared with the user, regardless of
            // nesting depth in the owner's tree (Google-Drive-style "Shared with me"),
            // so a single file shared without its parent folder stays discoverable.
            $folders = Folder::where('owner_id', '!=', $user->id)->sharedWithUser($user)->with('owner')->get();
            $files = FileEntry::where('owner_id', '!=', $user->id)->sharedWithUser($user)->with('owner')->get();
        } elseif ($tab === 'all' && $user->can('files.view_all')) {
            $folders = Folder::whereNull('parent_id')->with('owner')->get();
            $files = FileEntry::whereNull('folder_id')->with('owner')->get();
        } else {
            $tab = 'mine';
            $folders = Folder::whereNull('parent_id')->where('owner_id', $user->id)->with('owner')->get();
            $files = FileEntry::whereNull('folder_id')->where('owner_id', $user->id)->with('owner')->get();
        }

        $moveDestinations = $user->can('files.manage')
            ? Folder::orderBy('name')->get(['id', 'name'])
            : Folder::where('owner_id', $user->id)->orderBy('name')->get(['id', 'name']);

        return view('files.index', [
            'tab' => $tab,
            'currentFolder' => $currentFolder,
            'breadcrumb' => $breadcrumb,
            'folders' => $folders,
            'files' => $files,
            'moveDestinations' => $moveDestinations,
            'users' => User::orderBy('name')->get(['id', 'name']),
            'roles' => Role::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function trash(Request $request): View
    {
        $this->authorize('viewAny', Folder::class);

        $user = $request->user();
        $seeAll = $user->can('files.view_all');

        $folders = Folder::onlyTrashed()->when(! $seeAll, fn ($q) => $q->where('owner_id', $user->id))
            ->with('owner')->latest('deleted_at')->get();
        $files = FileEntry::onlyTrashed()->when(! $seeAll, fn ($q) => $q->where('owner_id', $user->id))
            ->with('owner')->latest('deleted_at')->get();

        return view('files.trash', ['folders' => $folders, 'files' => $files]);
    }
}
