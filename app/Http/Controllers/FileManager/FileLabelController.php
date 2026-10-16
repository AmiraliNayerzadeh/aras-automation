<?php

namespace App\Http\Controllers\FileManager;

use App\Http\Controllers\Controller;
use App\Models\FileManager\FileEntry;
use App\Models\FileManager\Folder;
use App\Models\Settings\LookupValue;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FileLabelController extends Controller
{
    public function syncForFile(Request $request, FileEntry $file): RedirectResponse
    {
        // Labels are shared workflow status (ordered / shipping / in warehouse...),
        // so anyone who can see an item may update them — every change is logged.
        $this->authorize('view', $file);
        $this->sync($request, $file);

        return redirect()->back()->with('status', 'labels-updated');
    }

    public function syncForFolder(Request $request, Folder $folder): RedirectResponse
    {
        $this->authorize('view', $folder);
        $this->sync($request, $folder);

        return redirect()->back()->with('status', 'labels-updated');
    }

    protected function sync(Request $request, FileEntry|Folder $item): void
    {
        $data = $request->validate([
            'labels' => ['nullable', 'array'],
            'labels.*' => ['integer'],
        ]);

        $allowedIds = LookupValue::whereHas('lookupType', fn ($q) => $q->where('code', 'file_label'))
            ->where('is_active', true)
            ->pluck('id');

        $item->load('labels');
        $currentIds = $item->labels->pluck('id');

        // Only touch active labels the caller picked; an inactive label already
        // on the item stays put (the form can't show it, so it never resubmits it).
        $requested = collect($data['labels'] ?? [])->intersect($allowedIds);
        $keptInactive = $currentIds->diff($allowedIds);

        $changes = $item->labels()->sync($requested->merge($keptInactive)->unique()->values()->all());

        if (! $changes['attached'] && ! $changes['detached']) {
            return;
        }

        $names = fn (array $ids) => LookupValue::whereIn('id', $ids)->get()
            ->map(fn (LookupValue $v) => $v->label[app()->getLocale()] ?? $v->code)->all();

        $noun = $item instanceof Folder ? 'Folder' : 'File';

        activity('file_manager')->causedBy($request->user())->performedOn($item)
            ->event('labels_updated')
            ->withProperties([
                'added' => $names($changes['attached']),
                'removed' => $names($changes['detached']),
            ])
            ->log("{$noun} labels updated");
    }
}
