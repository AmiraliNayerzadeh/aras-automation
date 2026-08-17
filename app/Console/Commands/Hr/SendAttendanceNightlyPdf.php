<?php

namespace App\Console\Commands\Hr;

use App\Enums\UserStatus;
use App\Models\User;
use App\Services\AttendanceCalculator;
use App\Services\TelegramNotifier;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

#[Signature('attendance:nightly-pdf-report')]
#[Description('Send a nightly PDF attendance report (photo, in/out, hours, overtime/shortfall) to Telegram.')]
class SendAttendanceNightlyPdf extends Command
{
    public function handle(AttendanceCalculator $calculator, TelegramNotifier $telegram): void
    {
        $date = now()->startOfDay();

        $users = User::query()
            ->with('workShifts')
            ->where('status', UserStatus::Active->value)
            ->orderBy('name')
            ->get();

        $rows = $calculator->forDateRange($users, $date, $date)
            ->reject(fn (array $row) => $row['status'] === 'day_off')
            ->map(function (array $row) {
                $row['photo_data_uri'] = $this->photoDataUri($row['user']);

                return $row;
            })
            ->values();

        $pdf = Pdf::loadView('exports.attendance-nightly-pdf', [
            'date' => $date,
            'rows' => $rows,
        ])->setPaper('a4', 'landscape');

        $filename = 'attendance-'.$date->toDateString().'.pdf';

        $sent = $telegram->sendDocument(
            config('services.telegram.report_chat_id'),
            $filename,
            $pdf->output(),
            'Attendance report — '.$date->format('l, Y-m-d')
        );

        if ($sent) {
            Log::channel('telegram')->info('گزارش PDF شبانه حضور و غیاب ارسال شد', [
                'date' => $date->toDateString(),
                'employees' => $rows->count(),
            ]);
        } else {
            Log::channel('telegram')->error('ارسال گزارش PDF شبانه ناموفق بود', ['date' => $date->toDateString()]);
        }

        $this->info('Nightly attendance PDF: '.($sent ? 'sent' : 'failed').' ('.$rows->count().' rows)');
    }

    private function photoDataUri(User $user): ?string
    {
        if (! $user->profile_photo_path || ! Storage::disk('public')->exists($user->profile_photo_path)) {
            return null;
        }

        $path = Storage::disk('public')->path($user->profile_photo_path);
        $mime = mime_content_type($path) ?: 'image/jpeg';

        return 'data:'.$mime.';base64,'.base64_encode(file_get_contents($path));
    }
}
