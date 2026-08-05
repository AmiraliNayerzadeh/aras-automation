<?php

namespace App\Http\Controllers;

use App\Enums\AttachmentKind;
use App\Enums\RequestStatus;
use App\Models\Hr\LeaveRequest;
use App\Models\Settings\LookupValue;
use App\Models\User;
use App\Services\Workflow\ApprovalWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LeaveRequestController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', LeaveRequest::class);

        $query = LeaveRequest::with(['leaveType', 'user'])->latest();

        if (! $request->user()->can('leaves.view_all')) {
            $query->where('user_id', $request->user()->id);
        }

        $leaveRequests = $query->paginate(15);

        return view('requests.leaves.index', compact('leaveRequests'));
    }

    public function create(): View
    {
        $this->authorize('create', LeaveRequest::class);

        return view('requests.leaves.create', $this->formOptions());
    }

    public function store(Request $request, ApprovalWorkflowService $workflow): RedirectResponse
    {
        $this->authorize('create', LeaveRequest::class);

        $data = $this->validateData($request);
        $dayCount = $this->calculateDayCount($data);
        unset($data['attachment']);

        $leaveRequest = DB::transaction(function () use ($data, $dayCount, $request, $workflow) {
            $leaveRequest = LeaveRequest::create($data + [
                'user_id' => $request->user()->id,
                'day_count' => $dayCount,
                'status' => RequestStatus::Draft,
            ]);

            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $path = $file->store("requests/leave/{$leaveRequest->id}", 'public');

                $leaveRequest->attachments()->create([
                    'kind' => AttachmentKind::Attachment,
                    'file_path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getClientMimeType(),
                    'size_bytes' => $file->getSize(),
                    'uploaded_by_id' => $request->user()->id,
                ]);
            }

            $workflow->submit($leaveRequest, $request->user());

            return $leaveRequest;
        });

        return redirect()->route('leave-requests.show', $leaveRequest)->with('status', 'leave-submitted');
    }

    public function show(LeaveRequest $leaveRequest): View
    {
        $this->authorize('view', $leaveRequest);

        $leaveRequest->load(['leaveType', 'user', 'substitute', 'attachments', 'approvalSteps.approver', 'approvalSteps.actedBy']);

        return view('requests.leaves.show', compact('leaveRequest'));
    }

    public function cancel(Request $request, LeaveRequest $leaveRequest, ApprovalWorkflowService $workflow): RedirectResponse
    {
        $this->authorize('cancel', $leaveRequest);

        $workflow->cancel($leaveRequest, $request->user(), $request->string('reason')->toString() ?: null);

        return redirect()->route('leave-requests.show', $leaveRequest)->with('status', 'leave-cancelled');
    }

    protected function validateData(Request $request): array
    {
        return $request->validate([
            'lookup_value_id' => ['required', 'exists:lookup_values,id'],
            'substitute_user_id' => ['nullable', 'exists:users,id'],
            'from_date' => ['required', 'date'],
            'to_date' => ['required', 'date', 'after_or_equal:from_date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i', 'after:start_time'],
            'description' => ['nullable', 'string', 'max:2000'],
            'attachment' => ['nullable', 'file', 'max:10240'],
        ]);
    }

    protected function calculateDayCount(array $data): float
    {
        $from = Carbon::parse($data['from_date']);
        $to = Carbon::parse($data['to_date']);
        $days = $from->diffInDays($to) + 1;

        if ($days === 1 && ! empty($data['start_time']) && ! empty($data['end_time'])) {
            $hours = Carbon::parse($data['start_time'])->diffInMinutes(Carbon::parse($data['end_time'])) / 60;

            return round($hours / 8, 2);
        }

        return (float) $days;
    }

    protected function formOptions(): array
    {
        return [
            'leaveTypes' => LookupValue::whereHas('lookupType', fn ($q) => $q->where('code', 'leave_type'))
                ->where('is_active', true)->orderBy('sort_order')->get(),
            'colleagues' => User::orderBy('name')->get(['id', 'name']),
        ];
    }
}
