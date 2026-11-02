<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hr\AttendanceCorrection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;

class AttendanceCorrectionController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:correct-attendance'),
        ];
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'date' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'check_in' => ['nullable', 'date_format:H:i'],
            'check_out' => ['nullable', 'date_format:H:i', 'required_without:check_in', Rule::when($request->filled('check_in'), ['after:check_in'])],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $at = fn (?string $time) => $time ? $data['date'].' '.$time.':00' : null;

        $correction = AttendanceCorrection::where('user_id', $data['user_id'])
            ->whereDate('date', $data['date'])
            ->first() ?? new AttendanceCorrection(['user_id' => $data['user_id'], 'date' => $data['date']]);

        $correction->fill([
            'check_in' => $at($data['check_in'] ?? null),
            'check_out' => $at($data['check_out'] ?? null),
            'reason' => $data['reason'] ?? null,
            'edited_by' => $request->user()->id,
        ])->save();

        return back()->with('status', 'attendance-corrected');
    }

    public function destroy(AttendanceCorrection $attendanceCorrection): RedirectResponse
    {
        $attendanceCorrection->delete();

        return back()->with('status', 'attendance-correction-removed');
    }
}
