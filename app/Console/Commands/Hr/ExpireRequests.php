<?php

namespace App\Console\Commands\Hr;

use App\Enums\ApprovalStepStatus;
use App\Enums\RequestStatus;
use App\Models\Hr\LeaveRequest;
use App\Models\Hr\MissionRequest;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;

#[Signature('hr:expire-requests')]
#[Description('Mark leave/mission requests as expired once their start date has passed without a decision.')]
class ExpireRequests extends Command
{
    public function handle(): void
    {
        $count = 0;
        $count += $this->expire(LeaveRequest::query());
        $count += $this->expire(MissionRequest::query());

        $this->info("Expired {$count} request(s).");
    }

    protected function expire($query): int
    {
        $requests = $query
            ->where('status', RequestStatus::Pending->value)
            ->where('from_date', '<', now()->toDateString())
            ->get();

        foreach ($requests as $request) {
            /** @var Model $request */
            $request->approvalSteps()
                ->where('status', ApprovalStepStatus::Pending->value)
                ->update([
                    'status' => ApprovalStepStatus::Skipped,
                    'system_note' => 'Chain stopped: request expired.',
                ]);

            $request->update(['status' => RequestStatus::Expired, 'decided_at' => now()]);
        }

        return $requests->count();
    }
}
