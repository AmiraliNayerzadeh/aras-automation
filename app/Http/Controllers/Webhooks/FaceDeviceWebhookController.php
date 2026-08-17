<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\Hr\FaceDeviceEvent;
use App\Models\User;
use App\Services\SettingsService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FaceDeviceWebhookController extends Controller
{
    /**
     * Receive a push event notification from the DS-K1T342MFX face recognition terminal
     * (Hikvision ISAPI "Notify Surveillance Center" feature).
     */
    public function store(Request $request, string $token): Response
    {
        if (! hash_equals((string) config('services.face_device.webhook_token'), $token)) {
            abort(404);
        }

        if (! $this->ipAllowed($request)) {
            Log::channel('telegram')->warning('face-device: رد شد، آی‌پی مجاز نیست', [
                'ip' => $request->ip(),
            ]);

            return response('', 200);
        }

        try {
            $data = $this->extractPayload($request);

            if (! $data) {
                Log::channel('telegram')->warning('face-device: درخواستی دریافت شد اما محتوایی برای پردازش پیدا نشد', [
                    'content_type' => $request->header('Content-Type'),
                    'ip' => $request->ip(),
                    'diagnostics' => $this->buildDiagnostics($request),
                ]);

                return response('', 200);
            }

            if ($this->isHeartbeat($data)) {
                return response('', 200);
            }

            $event = $this->storeEvent($request, $data);

            if (app(SettingsService::class)->get('attendance_send_event_logs', true)) {
                Log::channel('telegram')->info('رویداد جدید از دستگاه تشخیص چهره ثبت شد', [
                    'employee_no' => $event->employee_no,
                    'user' => $event->user?->name,
                    'event' => $event->minor_event ?? $event->major_event,
                    'verify_mode' => $event->verify_mode,
                    'time' => $event->event_time?->toDateTimeString(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::channel('telegram')->error('خطا در پردازش رویداد دستگاه تشخیص چهره', [
                'message' => $e->getMessage(),
                'file' => $e->getFile().':'.$e->getLine(),
            ]);
        }

        return response('', 200);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function isHeartbeat(array $data): bool
    {
        $eventType = $data['eventType'] ?? null;

        return is_string($eventType) && strcasecmp($eventType, 'heartBeat') === 0;
    }

    private function ipAllowed(Request $request): bool
    {
        $allowed = array_filter(array_map('trim', explode(',', (string) config('services.face_device.allowed_ips'))));

        if (empty($allowed)) {
            return true;
        }

        return in_array($request->ip(), $allowed, true);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function extractPayload(Request $request): ?array
    {
        if (str_starts_with((string) $request->header('Content-Type'), 'multipart/form-data')) {
            // Hikvision terminals don't reliably set a filename or a
            // text/xml|json mime type on the event-data part (and may send
            // either XML or JSON depending on device config), so sniff every
            // part's raw content instead of trusting mime type / filename.
            foreach ($request->allFiles() as $file) {
                $files = is_array($file) ? $file : [$file];

                foreach ($files as $uploaded) {
                    $content = $this->stripBom(trim((string) file_get_contents($uploaded->getRealPath())));

                    if ($parsed = $this->parsePayloadString($content)) {
                        return $parsed;
                    }
                }
            }

            foreach ($request->all() as $value) {
                if (is_string($value) && $parsed = $this->parsePayloadString($this->stripBom(trim($value)))) {
                    return $parsed;
                }
            }

            return null;
        }

        return $this->parsePayloadString($this->stripBom(trim((string) $request->getContent())));
    }

    /**
     * @return array<string, mixed>|null
     */
    private function parsePayloadString(string $content): ?array
    {
        if ($content === '') {
            return null;
        }

        if (str_starts_with($content, '<')) {
            $parsed = @simplexml_load_string($content);

            return $parsed === false ? null : json_decode((string) json_encode($parsed), true);
        }

        if (str_starts_with($content, '{') || str_starts_with($content, '[')) {
            $decoded = json_decode($content, true);

            return is_array($decoded) ? $decoded : null;
        }

        return null;
    }

    private function stripBom(string $value): string
    {
        return str_starts_with($value, "\xEF\xBB\xBF") ? substr($value, 3) : $value;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildDiagnostics(Request $request): array
    {
        $files = [];

        foreach ($request->allFiles() as $field => $file) {
            $list = is_array($file) ? $file : [$file];

            foreach ($list as $uploaded) {
                $files[] = [
                    'field' => $field,
                    'name' => $uploaded->getClientOriginalName(),
                    'mime' => $uploaded->getMimeType(),
                    'size' => $uploaded->getSize(),
                    'preview' => mb_substr((string) file_get_contents($uploaded->getRealPath()), 0, 150),
                ];
            }
        }

        $fields = [];

        foreach ($request->all() as $key => $value) {
            $fields[$key] = is_string($value) ? mb_substr($value, 0, 150) : gettype($value);
        }

        return ['files' => $files, 'fields' => $fields];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function storeEvent(Request $request, array $data): FaceDeviceEvent
    {
        $access = $data['AccessControllerEvent'] ?? [];

        $employeeNo = $access['employeeNoString'] ?? $access['employeeNo'] ?? null;
        $eventTime = $data['dateTime'] ?? null;

        $picturePath = $this->storePicture($request);

        $user = $employeeNo
            ? User::query()->where('employee_number', $employeeNo)->first()
            : null;

        return FaceDeviceEvent::create([
            'user_id' => $user?->id,
            'employee_no' => $employeeNo,
            'person_name' => $access['name'] ?? null,
            'device_serial' => $data['deviceID'] ?? $data['macAddress'] ?? null,
            'device_ip' => $request->ip(),
            'major_event' => $access['majorEventType'] ?? $data['eventType'] ?? null,
            'minor_event' => $access['subEventType'] ?? null,
            'verify_mode' => $access['currentVerifyMode'] ?? null,
            'attendance_status' => $access['attendanceStatus'] ?? null,
            'event_time' => $eventTime ? Carbon::parse($eventTime) : now(),
            'picture_path' => $picturePath,
            'raw_payload' => $data,
        ]);
    }

    private function storePicture(Request $request): ?string
    {
        foreach ($request->allFiles() as $file) {
            $files = is_array($file) ? $file : [$file];

            foreach ($files as $uploaded) {
                $mime = $uploaded->getMimeType();

                if ($mime && str_starts_with($mime, 'image/')) {
                    $path = 'face-events/'.now()->format('Y-m-d').'/'.Str::uuid().'.jpg';
                    Storage::disk('public')->put($path, file_get_contents($uploaded->getRealPath()));

                    return $path;
                }
            }
        }

        return null;
    }
}
