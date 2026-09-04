<?php

namespace App\Http\Controllers\FileManager;

use App\Http\Controllers\Controller;
use App\Models\FileManager\FileEntry;
use App\Models\FileManager\FileFavorite;
use App\Models\FileManager\Folder;
use App\Models\Organization\Department;
use App\Models\Organization\Position;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
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
        $view = $request->input('view') === 'list' ? 'list' : 'grid';

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

        $this->attachAccessAvatars($folders);
        $this->attachAccessAvatars($files);

        $moveDestinations = $user->can('files.manage')
            ? Folder::orderBy('name')->get(['id', 'name'])
            : Folder::where('owner_id', $user->id)->orderBy('name')->get(['id', 'name']);

        $favoriteKeys = FileFavorite::where('user_id', $user->id)
            ->get(['favoritable_type', 'favoritable_id'])
            ->map(fn (FileFavorite $f) => $f->favoritable_type.':'.$f->favoritable_id)
            ->all();

        $quickAccess = FileFavorite::where('user_id', $user->id)
            ->with('favoritable')
            ->latest()
            ->get()
            ->pluck('favoritable')
            ->filter();

        return view('files.index', [
            'tab' => $tab,
            'view' => $view,
            'currentFolder' => $currentFolder,
            'breadcrumb' => $breadcrumb,
            'folders' => $folders,
            'files' => $files,
            'moveDestinations' => $moveDestinations,
            'users' => User::orderBy('name')->get(['id', 'name']),
            'roles' => Role::orderBy('name')->get(['id', 'name']),
            'departments' => Department::orderBy('name')->get(['id', 'name']),
            'positions' => Position::orderBy('title')->get(['id', 'title']),
            'favoriteKeys' => $favoriteKeys,
            'quickAccess' => $quickAccess,
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

    /**
     * Attaches, to each item, up to 5 avatars of the people who have direct
     * "user" access (owner + user-type shares) and a count of any remaining
     * access (extra users, or group shares via role/department/position/everyone),
     * resolved with a single bulk query per collection to avoid N+1s.
     *
     * @param  Collection<int, Folder|FileEntry>  $items
     */
    protected function attachAccessAvatars(Collection $items): void
    {
        if ($items->isEmpty()) {
            return;
        }

        $items->load('shares');

        $userIds = $items
            ->flatMap(fn ($item) => collect([$item->owner_id])
                ->merge($item->shares->where('grantee_type', 'user')->pluck('grantee_id')))
            ->unique()
            ->filter();

        $users = User::whereIn('id', $userIds)->get(['id', 'name', 'profile_photo_path'])->keyBy('id');

        $items->each(function ($item) use ($users) {
            $ids = collect([$item->owner_id])
                ->merge($item->shares->where('grantee_type', 'user')->pluck('grantee_id'))
                ->unique()
                ->values();

            $item->setAttribute('access_avatars', $ids->take(5)->map(fn ($id) => $users->get($id))->filter()->values());
            $item->setAttribute('access_avatars_extra', max(0, $ids->count() - 5));
            $item->setAttribute(
                'access_group_shares',
                $item->shares->whereIn('grantee_type', ['role', 'department', 'position', 'everyone'])->count()
            );
        });
    }
}
