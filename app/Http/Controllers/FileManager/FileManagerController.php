<?php

namespace App\Http\Controllers\FileManager;

use App\Http\Controllers\Controller;
use App\Models\FileManager\FileEntry;
use App\Models\FileManager\FileFavorite;
use App\Models\FileManager\Folder;
use App\Models\Organization\Department;
use App\Models\Organization\Position;
use App\Models\Settings\LookupValue;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class FileManagerController extends Controller
{
    public const SORTS = ['newest', 'oldest', 'name_asc', 'name_desc'];

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Folder::class);

        $user = $request->user();
        $folderId = $request->integer('folder') ?: null;

        // The view mode (grid/list), sort order and tab persist across requests via cookie,
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

        $sort = $request->input('sort', $request->cookie('files_sort', 'newest'));
        $sort = in_array($sort, self::SORTS, true) ? $sort : 'newest';

        if ($request->has('sort')) {
            Cookie::queue('files_sort', $sort, 60 * 24 * 365);
        }

        $currentFolder = null;
        $breadcrumb = collect();

        // Label filter: while active (and not inside a folder) it searches every
        // item you can see regardless of nesting, so a labelled file buried in a
        // subfolder is still found — "show me everything that's in transit".
        $labelFilterId = $request->integer('label') ?: null;
        $withLabel = fn ($q) => $labelFilterId ? $q->withLabel($labelFilterId) : $q;
        $topLevelOnly = fn ($q, string $column) => $labelFilterId ? $q : $q->whereNull($column);
        $sorted = fn ($q) => $this->applySort($q, $sort);

        if ($folderId) {
            $currentFolder = Folder::findOrFail($folderId);
            $this->authorize('view', $currentFolder);

            // Even inside a folder you can see, an individual child/file with its
            // own (more restrictive) share must stay hidden unless you're one of
            // its explicit grantees - folder access never overrides an item's own
            // sharing.
            $folders = $withLabel($currentFolder->children()->visibleTo($user))->with(['owner', 'labels'])->tap($sorted)->get();
            $files = $withLabel($currentFolder->files()->visibleTo($user))->with(['owner', 'labels'])->tap($sorted)->get();
            $breadcrumb = $currentFolder->ancestors()->push($currentFolder);
        } elseif ($tab === 'shared') {
            // Lists every folder/file directly shared with the user, regardless of
            // nesting depth in the owner's tree (Google-Drive-style "Shared with me"),
            // so a single file shared without its parent folder stays discoverable.
            $folders = $withLabel(Folder::where('owner_id', '!=', $user->id)->sharedWithUser($user))->with(['owner', 'labels'])->tap($sorted)->get();
            $files = $withLabel(FileEntry::where('owner_id', '!=', $user->id)->sharedWithUser($user))->with(['owner', 'labels'])->tap($sorted)->get();
        } elseif ($tab === 'all') {
            // Open to everyone: unrestricted (no-share) items are visible to the
            // whole company by default; items with an explicit share are scoped
            // to their owner/grantees; files.view_all admins always see everything.
            $folders = $withLabel($topLevelOnly(Folder::query(), 'parent_id')->visibleTo($user))->with(['owner', 'labels'])->tap($sorted)->get();
            $files = $withLabel($topLevelOnly(FileEntry::query(), 'folder_id')->visibleTo($user))->with(['owner', 'labels'])->tap($sorted)->get();
        } else {
            $tab = 'mine';
            $folders = $withLabel($topLevelOnly(Folder::where('owner_id', $user->id), 'parent_id'))->with(['owner', 'labels'])->tap($sorted)->get();
            $files = $withLabel($topLevelOnly(FileEntry::where('owner_id', $user->id), 'folder_id'))->with(['owner', 'labels'])->tap($sorted)->get();
        }

        $this->attachAccessAvatars($folders);
        $this->attachAccessAvatars($files);

        // The tree's levels along the current path are embedded in the page so
        // it renders expanded down to the current folder without extra round
        // trips; every other branch is fetched on demand via tree(). The walk
        // stops at the first ancestor the user can't see (e.g. a folder shared
        // without its parent) rather than revealing anything above it.
        $treeLevels = ['root' => $this->treeLevel($user, null)];
        $treePath = [];
        $previousLevel = $treeLevels['root'];

        foreach ($breadcrumb as $crumb) {
            if (! collect($previousLevel['folders'])->contains('id', $crumb->id)) {
                $treePath = [];
                break;
            }

            $treePath[] = $crumb->id;
            $previousLevel = $treeLevels[$crumb->id] = $this->treeLevel($user, $crumb);
        }

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
            'fileLabels' => LookupValue::whereHas('lookupType', fn ($q) => $q->where('code', 'file_label'))
                ->where('is_active', true)->orderBy('sort_order')->get(),
            'labelFilterId' => $labelFilterId,
            'sort' => $sort,
            'treeLevels' => $treeLevels,
            'treePath' => $treePath,
            'treeHidden' => $request->cookie('files_tree') === 'hidden',
        ]);
    }

    /**
     * One level of the folder tree (lazy-loaded as the user expands a node).
     */
    public function tree(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Folder::class);

        $folder = null;

        if ($request->filled('folder')) {
            $folder = Folder::findOrFail($request->integer('folder'));
            $this->authorize('view', $folder);
        }

        return response()->json($this->treeLevel($request->user(), $folder));
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
     * The visible subfolders and files directly inside $parent (or at the top
     * level), using the same visibility rules as the listing, so the tree never
     * shows anything the main view wouldn't. `has_children` is resolved in two
     * bulk queries so each folder knows whether to offer an expand toggle.
     *
     * @return array{folders: list<array<string, mixed>>, files: list<array<string, mixed>>}
     */
    protected function treeLevel(User $user, ?Folder $parent): array
    {
        $inParent = fn ($q, string $column) => $parent ? $q->where($column, $parent->id) : $q->whereNull($column);

        $folders = $inParent(Folder::query(), 'parent_id')->visibleTo($user)
            ->orderBy('name')->orderBy('id')
            ->get(['id', 'name', 'is_confidential']);

        $files = $inParent(FileEntry::query(), 'folder_id')->visibleTo($user)
            ->tap(fn ($q) => $this->applySort($q, 'name_asc'))
            ->get(['id', 'title', 'original_name', 'mime_type', 'is_confidential']);

        $folderIds = $folders->pluck('id');
        $nonEmptyIds = $folderIds->isEmpty() ? collect() : Folder::whereIn('parent_id', $folderIds)->visibleTo($user)->distinct()->pluck('parent_id')
            ->merge(FileEntry::whereIn('folder_id', $folderIds)->visibleTo($user)->distinct()->pluck('folder_id'))
            ->flip();

        return [
            'folders' => $folders->map(fn (Folder $folder) => [
                'id' => $folder->id,
                'name' => $folder->name,
                'confidential' => $folder->is_confidential,
                'has_children' => $nonEmptyIds->has($folder->id),
            ])->values()->all(),
            'files' => $files->map(fn (FileEntry $file) => [
                'id' => $file->id,
                'name' => $file->title ?: $file->original_name,
                'icon' => $file->iconClass().' '.$file->iconColorClass(),
                'url' => route('files.entries.show', $file),
                'confidential' => $file->is_confidential,
            ])->values()->all(),
        ];
    }

    /**
     * Folders sort by name; files by their displayed name (title, falling back
     * to the original file name — the same rule the views use). The id
     * tie-breaker keeps the order stable when names/timestamps are equal.
     *
     * @param  Builder<Folder|FileEntry>  $query
     */
    protected function applySort(Builder $query, string $sort): void
    {
        $model = $query->getModel();
        $nameExpression = $model instanceof FileEntry
            ? 'COALESCE(NULLIF('.$model->qualifyColumn('title').", ''), ".$model->qualifyColumn('original_name').')'
            : $model->qualifyColumn('name');

        match ($sort) {
            'oldest' => $query->oldest($model->qualifyColumn('created_at'))->orderBy($model->getQualifiedKeyName()),
            'name_asc' => $query->orderByRaw($nameExpression.' asc')->orderBy($model->getQualifiedKeyName()),
            'name_desc' => $query->orderByRaw($nameExpression.' desc')->orderByDesc($model->getQualifiedKeyName()),
            default => $query->latest($model->qualifyColumn('created_at'))->orderByDesc($model->getQualifiedKeyName()),
        };
    }

    /**
     * Attaches, to each item, up to 5 avatars of the people who have direct
     * "user" access and a count of any remaining access (extra users, or
     * group shares via role/department/position/everyone), resolved with a
     * single bulk query per collection to avoid N+1s.
     *
     * An item with no explicit shares is open to everyone by default, so
     * there's nothing meaningful to show here — displaying just the owner's
     * avatar would wrongly suggest the item is restricted to them.
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
            ->filter(fn ($item) => $item->shares->isNotEmpty())
            ->flatMap(fn ($item) => collect([$item->owner_id])
                ->merge($item->shares->where('grantee_type', 'user')->pluck('grantee_id')))
            ->unique()
            ->filter();

        $users = User::whereIn('id', $userIds)->get(['id', 'name', 'profile_photo_path'])->keyBy('id');

        $items->each(function ($item) use ($users) {
            if ($item->shares->isEmpty()) {
                return;
            }

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
