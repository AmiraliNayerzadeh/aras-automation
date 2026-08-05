<?php

namespace App\Services\Workflow;

use App\Contracts\Approvable;
use App\Enums\ApprovalStepRole;
use App\Enums\ApprovalStepStatus;
use App\Enums\RequestStatus;
use App\Models\User;
use App\Models\Workflow\ApprovalStep;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ApprovalWorkflowService
{
    /**
     * @param  Approvable&Model  $request
     */
    public function submit(Approvable $request, User $actor): void
    {
        $this->assert($request->status === RequestStatus::Draft, 'Only draft requests can be submitted.');
        $this->assert($actor->is($request->requester()), 'Only the requester can submit this request.');

        DB::transaction(function () use ($request) {
            $request->update(['status' => RequestStatus::Submitted]);

            $order = 1;
            $allResolved = true;

            foreach ($request->approvalStepRoles() as $role) {
                $outcome = $this->resolveStepOutcome($role, $request->requester());

                $request->approvalSteps()->create([
                    'step_order' => $order++,
                    'role' => $role,
                    'approver_id' => $outcome['approver_id'],
                    'status' => $outcome['status'],
                    'system_note' => $outcome['system_note'],
                    'acted_at' => $outcome['status'] === ApprovalStepStatus::Approved ? now() : null,
                ]);

                if ($outcome['status'] === ApprovalStepStatus::Pending) {
                    $allResolved = false;
                }
            }

            $request->update([
                'status' => $allResolved ? RequestStatus::Approved : RequestStatus::Pending,
                'submitted_at' => now(),
                'decided_at' => $allResolved ? now() : null,
            ]);
        });
    }

    public function approve(ApprovalStep $step, User $actor, ?string $comment = null): void
    {
        /** @var Approvable&Model $request */
        $request = $step->subject;

        $this->assertIsCurrentStep($request, $step);
        $this->assertCanAct($request, $step, $actor);

        DB::transaction(function () use ($step, $actor, $comment, $request) {
            $step->update([
                'status' => ApprovalStepStatus::Approved,
                'acted_by_id' => $actor->id,
                'acted_at' => now(),
                'comment' => $comment,
            ]);

            $request->load('approvalSteps');

            if (! $request->currentStep()) {
                $request->update(['status' => RequestStatus::Approved, 'decided_at' => now()]);
            }
        });
    }

    public function reject(ApprovalStep $step, User $actor, string $comment): void
    {
        /** @var Approvable&Model $request */
        $request = $step->subject;

        $this->assertIsCurrentStep($request, $step);
        $this->assertCanAct($request, $step, $actor);

        DB::transaction(function () use ($step, $actor, $comment, $request) {
            $step->update([
                'status' => ApprovalStepStatus::Rejected,
                'acted_by_id' => $actor->id,
                'acted_at' => now(),
                'comment' => $comment,
            ]);

            $request->approvalSteps()
                ->where('status', ApprovalStepStatus::Pending->value)
                ->update([
                    'status' => ApprovalStepStatus::Skipped,
                    'system_note' => 'Chain stopped: request rejected.',
                ]);

            $request->update(['status' => RequestStatus::Rejected, 'decided_at' => now()]);
        });
    }

    /**
     * @param  Approvable&Model  $request
     */
    public function cancel(Approvable $request, User $actor, ?string $reason = null): void
    {
        $isOwner = $actor->is($request->requester());
        $canCancelAny = $actor->can($request->permissionPrefix().'.cancel_any');

        $this->assert($isOwner || $canCancelAny, 'You are not allowed to cancel this request.');
        $this->assert(! $request->status->isTerminal(), 'This request has already been decided.');

        DB::transaction(function () use ($request, $actor, $reason) {
            $request->approvalSteps()
                ->where('status', ApprovalStepStatus::Pending->value)
                ->update([
                    'status' => ApprovalStepStatus::Skipped,
                    'system_note' => 'Chain stopped: request cancelled.',
                ]);

            $request->update(['status' => RequestStatus::Cancelled, 'decided_at' => now()]);

            activity()
                ->performedOn($request)
                ->causedBy($actor)
                ->withProperties(['reason' => $reason])
                ->log('cancelled');
        });
    }

    /**
     * The currently-actionable steps assigned to the given user, across every
     * request type registered in the polymorphic morph map.
     *
     * @return Collection<int, ApprovalStep>
     */
    public function pendingStepsFor(User $user): Collection
    {
        $rolePoolValues = [];

        if ($user->hasAnyRole(['hr', 'admin', 'super-admin'])) {
            $rolePoolValues[] = ApprovalStepRole::Hr->value;
        }

        if ($user->hasRole('super-admin')) {
            $rolePoolValues[] = ApprovalStepRole::Ceo->value;
        }

        return ApprovalStep::query()
            ->where('status', ApprovalStepStatus::Pending->value)
            ->where(function ($query) use ($user, $rolePoolValues) {
                $query->where('approver_id', $user->id);

                if (! empty($rolePoolValues)) {
                    $query->orWhere(function ($q) use ($rolePoolValues) {
                        $q->whereNull('approver_id')->whereIn('role', $rolePoolValues);
                    });
                }
            })
            ->with('subject.approvalSteps', 'subject.user')
            ->latest()
            ->get()
            ->filter(fn (ApprovalStep $step) => $step->subject && $step->subject->currentStep()?->is($step))
            ->values();
    }

    /**
     * @return array{approver_id: ?int, status: ApprovalStepStatus, system_note: ?string}
     */
    protected function resolveStepOutcome(ApprovalStepRole $role, User $requester): array
    {
        $pool = $this->resolvePool($role, $requester);

        if ($pool->isEmpty()) {
            if ($role === ApprovalStepRole::Ceo) {
                return [
                    'approver_id' => null,
                    'status' => ApprovalStepStatus::Approved,
                    'system_note' => 'Auto-approved: no eligible approver at this step.',
                ];
            }

            return [
                'approver_id' => null,
                'status' => ApprovalStepStatus::Skipped,
                'system_note' => 'No eligible approver at this step.',
            ];
        }

        return [
            'approver_id' => $pool->count() === 1 ? $pool->first()->id : null,
            'status' => ApprovalStepStatus::Pending,
            'system_note' => null,
        ];
    }

    /**
     * @return Collection<int, User>
     */
    protected function resolvePool(ApprovalStepRole $role, User $requester): Collection
    {
        return match ($role) {
            ApprovalStepRole::Manager => $this->resolveManagerPool($requester),
            ApprovalStepRole::Hr => $this->resolveHrPool($requester),
            ApprovalStepRole::Ceo => $this->excludeRequester(User::role('super-admin')->get(), $requester),
        };
    }

    /**
     * @return Collection<int, User>
     */
    protected function resolveManagerPool(User $requester): Collection
    {
        $manager = $requester->manager;

        if (! $manager || $manager->is($requester)) {
            return collect();
        }

        return collect([$manager]);
    }

    /**
     * @return Collection<int, User>
     */
    protected function resolveHrPool(User $requester): Collection
    {
        $hr = $this->excludeRequester(User::role('hr')->get(), $requester);

        if ($hr->isNotEmpty()) {
            return $hr;
        }

        return $this->excludeRequester(User::role(['admin', 'super-admin'])->get(), $requester);
    }

    /**
     * @param  Collection<int, User>  $users
     * @return Collection<int, User>
     */
    protected function excludeRequester(Collection $users, User $requester): Collection
    {
        return $users->reject(fn (User $user) => $user->is($requester))->values();
    }

    /**
     * @param  Approvable&Model  $request
     */
    protected function assertIsCurrentStep(Approvable $request, ApprovalStep $step): void
    {
        $request->load('approvalSteps');
        $current = $request->currentStep();

        $this->assert($current && $current->is($step), 'This step is not currently actionable.', 422);
    }

    /**
     * @param  Approvable&Model  $request
     */
    protected function assertCanAct(Approvable $request, ApprovalStep $step, User $actor): void
    {
        if ($actor->can($request->permissionPrefix().'.approve_any')) {
            return;
        }

        if ($step->approver_id) {
            $this->assert($actor->is($step->approver), 'You are not the assigned approver for this step.');

            return;
        }

        $allowedRoles = match ($step->role) {
            ApprovalStepRole::Manager => [],
            ApprovalStepRole::Hr => ['hr', 'admin', 'super-admin'],
            ApprovalStepRole::Ceo => ['super-admin'],
        };

        $this->assert($actor->hasAnyRole($allowedRoles), 'You are not eligible to act on this step.');
    }

    protected function assert(bool $condition, string $message, int $status = 403): void
    {
        if (! $condition) {
            throw new HttpException($status, $message);
        }
    }
}
