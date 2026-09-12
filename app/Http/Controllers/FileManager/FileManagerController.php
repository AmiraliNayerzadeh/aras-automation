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
use Illuminate\Support\Facades\Cookie;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class FileManagerController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Folder::class);

        $user = $request->user();
        $folderId = $request->integer('folder') ?: null;

        // The view mode (grid/list) and tab persist across requests via cookie,
        // so a redirect after creating/uploading/moving something (which can't
        // carry every query param) still lands the user back where they were,
        // instead of silently resetting to the defaults.
        $tab = $request->string('tab', $request->cookie('files_tab', 'all'))->toString();
        $view = $request->input('view', $request->cookie('files_view', 'grid')) === 'list' ? 'list' : 'grid';

        if ($request->has('tab')) {
            Cookie::queue('files_tab', $tab, 60 * 24 * 365);
        }

        if ($request->has('view')) {
            Cookie::queue('files_view', $view, 60 * 24 * 365);
        }

        $currentFolder = null;
        $breadcrumb = collect();

        if ($folderId) {
            $currentFolder = Folder::findOrFail($folderId);
            $this->authorize('view', $currentFolder);

            // Even inside a folder you can see, an individual child/file with its
            // own (more restrictive) share must stay hidden unless you're one of
            // its explicit grantees - folder access never overrides an item's own
            // sharing.
            $folders = $currentFolder->children()->visibleTo($user)->with('owner')->get();
            $files = $currentFolder->files()->visibleTo($user)->with('owner')->get();
            $breadcrumb = $currentFolder->ancestors()->push($currentFolder);
        } elseif ($tab === 'shared') {
            // Lists every folder/file directly shared with the user, regardless of
            // nesting depth in the owner's tree (Google-Drive-style "Shared with me"),
            // so a single file shared without its parent folder stays discoverable.
            $folders = Folder::where('owner_id', '!=', $user->id)->sharedWithUser($user)->with('owner')->get();
            $files = FileEntry::where('owner_id', '!=', $user->id)->sharedWithUser($user)->with('owner')->get();
        } elseif ($tab === 'all') {
            // Open to everyone: unrestricted (no-share) items are visible to the
            // whole company by default; items with an explicit share are scoped
            // to their owner/grantees; files.view_all admins always see everything.
            $folders = Folder::whereNull('parent_id')->visibleTo($user)->with('owner')->get();
            $files = FileEntry::whereNull('folder_id')->visibleTo($user)->with('owner')->get();
        } else {
            $tab = 'mine';
            $folders = Folder::whereNull('parent_id')->where('owner_id', $user->id)->with('owner')->get();
            $files = FileEntry::whereNull('folder_id')->where('owner_id', $user->id)->with('owner')->get();
        }

        $this->attachAccessAvatars($folders);
        $this->attachAccessAvatars($files);

        // A confidential folder's name must not leak into this dropdown for a
        // files.manage holder who isn't its owner — same rule as everywhere else.
        $moveDestinations = $user->can('files.manage')
            ? Folder::where(fn ($q) => $q->where('is_confidential', false)->orWhere('owner_id', $user->id))
                ->orderBy('name')->get(['id', 'name'])
            : Folder::where('owner_id', $user->id)->orderBy('name')->get(['id', 'name']);

        $favoriteKeys = FileFavorite::where('user_id', $user->id)
            ->get(['favoritable_type', 'favoritable_id'])
            ->map(fn (FileFavorite $f) => $f->favoritable_type.':'.$f->favoritable_id)
            ->all();

        // Re-checked against current visibility on every load: a pinned item
        // whose sharing changed (or was marked confidential) after it was
        // pinned must not keep leaking its name/existence here.
        $quickAccess = FileFavorite::where('user_id', $user->id)
            ->with('favoritable')
            ->latest()
            ->get()
            ->pluck('favoritable')
            ->filter()
            ->filter(fn ($item) => $item->isVisibleTo($user))
            ->values();

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

        // files.view_all still doesn't reach a confidential item's trash entry —
        // same absolute rule as everywhere else.
        $confidentialAware = fn ($q) => $q->where(
            fn ($qq) => $qq->where('is_confidential', false)->orWhere('owner_id', $user->id)
        );

        $folders = Folder::onlyTrashed()
            ->when($seeAll, $confidentialAware, fn ($q) => $q->where('owner_id', $user->id))
            ->with('owner')->latest('deleted_at')->get();
        $files = FileEntry::onlyTrashed()
            ->when($seeAll, $confidentialAware, fn ($q) => $q->where('owner_id', $user->id))
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
