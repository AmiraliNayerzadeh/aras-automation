<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hr\WorkShift;
use App\Models\User;
use App\Support\WorkScheduleResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class WorkShiftController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:work-shifts.manage'),
        ];
    }

    public function index(): View
    {
        $users = User::query()
            ->withCount('workShifts')
            ->orderBy('name')
            ->get();

        return view('admin.work-shifts.index', [
            'users' => $users,
        ]);
    }

    public function edit(User $user, WorkScheduleResolver $resolver): View
    {
        return view('admin.work-shifts.edit', [
            'employee' => $user,
            'schedule' => $resolver->for($user),
            'isCustom' => $user->workShifts()->exists(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'is_remote' => ['sometimes', 'boolean'],
            'shifts' => ['required', 'array'],
            'shifts.*.is_day_off' => ['sometimes'],
            'shifts.*.start_time' => ['nullable', 'date_format:H:i'],
            'shifts.*.end_time' => ['nullable', 'date_format:H:i', 'after:shifts.*.start_time'],
        ]);

        foreach (range(0, 6) as $dayOfWeek) {
            $day = $data['shifts'][$dayOfWeek] ?? [];
            $isDayOff = (bool) ($day['is_day_off'] ?? false);

            WorkShift::updateOrCreate(
                ['user_id' => $user->id, 'day_of_week' => $dayOfWeek],
                [
                    'is_day_off' => $isDayOff,
                    'start_time' => $isDayOff ? null : ($day['start_time'] ?? null),
                    'end_time' => $isDayOff ? null : ($day['end_time'] ?? null),
                ]
            );
        }

        $user->update(['is_remote' => $request->boolean('is_remote')]);

        return redirect()->route('admin.work-shifts.index')->with('status', 'work-shift-updated');
    }
}
