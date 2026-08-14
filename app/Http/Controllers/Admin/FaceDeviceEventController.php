<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hr\FaceDeviceEvent;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class FaceDeviceEventController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:face-device-events.view'),
        ];
    }

    public function index(Request $request): View
    {
        $events = FaceDeviceEvent::query()
            ->with('user')
            ->when($request->filled('user_id'), fn ($q) => $q->where('user_id', $request->input('user_id')))
            ->when($request->filled('verify_mode'), fn ($q) => $q->where('verify_mode', $request->input('verify_mode')))
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('event_time', '>=', $request->input('date_from')))
            ->when($request->filled('date_to'), fn ($q) => $q->whereDate('event_time', '<=', $request->input('date_to')))
            ->latest('event_time')
            ->paginate(30)
            ->withQueryString();

        return view('admin.face-device-events.index', [
            'events' => $events,
            'users' => User::orderBy('name')->get(['id', 'name']),
            'verifyModes' => FaceDeviceEvent::query()->whereNotNull('verify_mode')->distinct()->pluck('verify_mode'),
        ]);
    }
}
