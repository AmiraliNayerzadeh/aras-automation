<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:activitylog.view'),
        ];
    }

    public function index(): View
    {
        $activities = Activity::with('causer')
            ->latest()
            ->paginate(30);

        return view('admin.activity-log.index', compact('activities'));
    }
}
