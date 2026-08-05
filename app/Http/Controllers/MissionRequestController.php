<?php

namespace App\Http\Controllers;

use App\Enums\AttachmentKind;
use App\Enums\RequestStatus;
use App\Models\Hr\MissionRequest;
use App\Models\Settings\LookupValue;
use App\Services\Workflow\ApprovalWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MissionRequestController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', MissionRequest::class);

        $query = MissionRequest::with(['missionType', 'user'])->latest();

        if (! $request->user()->can('missions.view_all')) {
            $query->where('user_id', $request->user()->id);
        }

        $missionRequests = $query->paginate(15);

        return view('requests.missions.index', compact('missionRequests'));
    }

    public function create(): View
    {
        $this->authorize('create', MissionRequest::class);

        return view('requests.missions.create', $this->formOptions());
    }

    public function store(Request $request, ApprovalWorkflowService $workflow): RedirectResponse
    {
        $this->authorize('create', MissionRequest::class);

        $data = $this->validateData($request);
        unset($data['receipt']);

        $missionRequest = DB::transaction(function () use ($data, $request, $workflow) {
            $missionRequest = MissionRequest::create($data + [
                'user_id' => $request->user()->id,
                'status' => RequestStatus::Draft,
            ]);

            if ($request->hasFile('receipt')) {
                $file = $request->file('receipt');
                $path = $file->store("requests/mission/{$missionRequest->id}", 'public');

                $missionRequest->attachments()->create([
                    'kind' => AttachmentKind::Receipt,
                    'file_path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getClientMimeType(),
                    'size_bytes' => $file->getSize(),
                    'uploaded_by_id' => $request->user()->id,
                ]);
            }

            $workflow->submit($missionRequest, $request->user());

            return $missionRequest;
        });

        return redirect()->route('mission-requests.show', $missionRequest)->with('status', 'mission-submitted');
    }

    public function show(MissionRequest $missionRequest): View
    {
        $this->authorize('view', $missionRequest);

        $missionRequest->load(['missionType', 'user', 'attachments', 'approvalSteps.approver', 'approvalSteps.actedBy']);

        return view('requests.missions.show', compact('missionRequest'));
    }

    public function report(Request $request, MissionRequest $missionRequest): RedirectResponse
    {
        $this->authorize('report', $missionRequest);

        $data = $request->validate([
            'actual_cost' => ['nullable', 'numeric', 'min:0'],
            'mission_report' => ['required', 'string', 'max:4000'],
        ]);

        $missionRequest->update($data);

        return redirect()->route('mission-requests.show', $missionRequest)->with('status', 'mission-report-saved');
    }

    public function cancel(Request $request, MissionRequest $missionRequest, ApprovalWorkflowService $workflow): RedirectResponse
    {
        $this->authorize('cancel', $missionRequest);

        $workflow->cancel($missionRequest, $request->user(), $request->string('reason')->toString() ?: null);

        return redirect()->route('mission-requests.show', $missionRequest)->with('status', 'mission-cancelled');
    }

    protected function validateData(Request $request): array
    {
        return $request->validate([
            'lookup_value_id' => ['required', 'exists:lookup_values,id'],
            'destination' => ['required', 'string', 'max:255'],
            'from_date' => ['required', 'date'],
            'to_date' => ['required', 'date', 'after_or_equal:from_date'],
            'purpose' => ['required', 'string', 'max:2000'],
            'outbound_transport' => ['nullable', 'string', 'max:255'],
            'return_transport' => ['nullable', 'string', 'max:255'],
            'estimated_cost' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'receipt' => ['nullable', 'file', 'max:10240'],
        ]);
    }

    protected function formOptions(): array
    {
        return [
            'missionTypes' => LookupValue::whereHas('lookupType', fn ($q) => $q->where('code', 'mission_type'))
                ->where('is_active', true)->orderBy('sort_order')->get(),
        ];
    }
}
