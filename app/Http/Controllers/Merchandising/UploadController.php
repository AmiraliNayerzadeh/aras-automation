<?php

namespace App\Http\Controllers\Merchandising;

use App\Http\Controllers\Controller;
use App\Models\Merchandising\Upload;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Resumable chunked uploads. Each chunk is a small raw request body, so no
 * PHP or web-server upload limit gets in the way of a long phone video, and a
 * dropped connection resumes from the last byte the server has.
 */
class UploadController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [new Middleware('permission:merchandising.create')];
    }

    public function start(Request $request): JsonResponse
    {
        $data = $request->validate([
            'kind' => ['required', Rule::in(Upload::KINDS)],
            'name' => ['required', 'string', 'max:255'],
            'size' => ['required', 'integer', 'min:1', 'max:'.Upload::MAX_BYTES],
            'type' => ['nullable', 'string', 'max:100'],
        ]);

        $token = (string) Str::uuid();

        Upload::create([
            'token' => $token,
            'user_id' => $request->user()->id,
            'kind' => $data['kind'],
            'original_name' => $data['name'],
            'mime_type' => $data['type'] ?? null,
            'size_bytes' => $data['size'],
            'temp_path' => "merchandising-uploads/{$token}.part",
        ]);

        Storage::disk('local')->put("merchandising-uploads/{$token}.part", '');

        return response()->json(['token' => $token, 'received' => 0, 'chunk_size' => Upload::CHUNK_BYTES], 201);
    }

    public function status(Request $request, string $token): JsonResponse
    {
        $upload = $this->find($request, $token);

        return response()->json(['received' => $upload->received_bytes, 'complete' => $upload->isComplete()]);
    }

    public function chunk(Request $request, string $token): JsonResponse
    {
        $upload = $this->find($request, $token);

        if ($upload->isComplete()) {
            return response()->json(['received' => $upload->received_bytes, 'complete' => true]);
        }

        // A retried chunk the server already has (or a gap) is answered with
        // the real position, and the client carries on from there.
        if ((int) $request->query('offset', -1) !== $upload->received_bytes) {
            return response()->json(['received' => $upload->received_bytes, 'complete' => false], 409);
        }

        $body = $request->getContent(true);
        $target = fopen(Storage::disk('local')->path($upload->temp_path), 'ab');
        $written = stream_copy_to_stream($body, $target, Upload::CHUNK_BYTES * 2);
        fclose($target);

        $received = $upload->received_bytes + (int) $written;

        if ($written === 0 || $received > $upload->size_bytes) {
            $this->truncate($upload);

            return response()->json(['received' => $upload->received_bytes, 'complete' => false], 422);
        }

        $upload->received_bytes = $received;

        if ($received === $upload->size_bytes) {
            $mime = mime_content_type(Storage::disk('local')->path($upload->temp_path)) ?: 'application/octet-stream';
            $allowed = $upload->kind === 'extra' ? '/^(image|video)\//' : '/^image\//';

            if (! preg_match($allowed, $mime)) {
                Storage::disk('local')->delete($upload->temp_path);
                $upload->delete();

                return response()->json(['message' => __('merchandising.upload_wrong_type')], 422);
            }

            $upload->mime_type = $mime;
            $upload->completed_at = now();
        }

        $upload->save();

        return response()->json(['received' => $upload->received_bytes, 'complete' => $upload->isComplete()]);
    }

    public function destroy(Request $request, string $token): JsonResponse
    {
        $upload = $this->find($request, $token);

        if ($upload->visit_id === null) {
            Storage::disk('local')->delete($upload->temp_path);
            $upload->delete();
        }

        return response()->json(['deleted' => true]);
    }

    private function find(Request $request, string $token): Upload
    {
        return Upload::where('token', $token)->where('user_id', $request->user()->id)->firstOrFail();
    }

    /**
     * Drops whatever a failed chunk wrote past the last good byte.
     */
    private function truncate(Upload $upload): void
    {
        $handle = fopen(Storage::disk('local')->path($upload->temp_path), 'r+b');
        ftruncate($handle, $upload->received_bytes);
        fclose($handle);
    }
}
