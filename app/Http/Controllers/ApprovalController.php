<?php

namespace App\Http\Controllers;

use App\Models\Workflow\ApprovalStep;
use App\Services\Workflow\ApprovalWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApprovalController extends Controller
{
    public function index(Request $request, ApprovalWorkflowService $workflow): View
    {
        $steps = $workflow->pendingStepsFor($request->user());

        return view('approvals.index', compact('steps'));
    }

    public function approve(Request $request, ApprovalStep $approvalStep, ApprovalWorkflowService $workflow): RedirectResponse
    {
        $workflow->approve($approvalStep, $request->user(), $request->string('comment')->toString() ?: null);

        return redirect()->route('approvals.index')->with('status', 'approval-approved');
    }

    public function reject(Request $request, ApprovalStep $approvalStep, ApprovalWorkflowService $workflow): RedirectResponse
    {
        $data = $request->validate([
            'comment' => ['required', 'string', 'max:2000'],
        ]);

        $workflow->reject($approvalStep, $request->user(), $data['comment']);

        return redirect()->route('approvals.index')->with('status', 'approval-rejected');
    }
}
