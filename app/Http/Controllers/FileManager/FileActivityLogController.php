<?php

namespace App\Http\Controllers\FileManager;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class FileActivityLogController extends Controller implements HasMiddleware
{
    protected const EVENTS = [
        'created', 'updated', 'deleted', 'restored',
        'downloaded', 'shared', 'unshared', 'share_link_enabled', 'share_link_disabled',
    ];

    public static function middleware(): array
    {
        return [
            new Middleware('permission:files.manage'),
        ];
    }

    public function index(Request $request): View
    {
        $activities = Activity::query()
            ->whereIn('subject_type', ['folder', 'file'])
            ->with(['causer', 'subject'])
            ->when($request->filled('event'), fn ($q) => $q->where('event', $request->input('event')))
            ->when($request->filled('causer_id'), fn ($q) => $q->where('causer_id', $request->input('causer_id')))
            ->when($request->filled('subject_type'), fn ($q) => $q->where('subject_type', $request->input('subject_type')))
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('created_at', '>=', $request->input('date_from')))
            ->when($request->filled('date_to'), fn ($q) => $q->whereDate('created_at', '<=', $request->input('date_to')))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('files.activity-log', [
            'activities' => $activities,
            'events' => self::EVENTS,
            'users' => User::orderBy('name')->get(['id', 'name']),
        ]);
    }
}
