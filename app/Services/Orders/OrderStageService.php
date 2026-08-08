<?php

namespace App\Services\Orders;

use App\Models\Orders\Order;
use App\Models\Orders\OrderStageLog;
use App\Models\Settings\LookupValue;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;

class OrderStageService
{
    /**
     * @return Collection<int, LookupValue>
     */
    public function orderedStages(): Collection
    {
        return LookupValue::whereHas('lookupType', fn ($q) => $q->where('code', 'order_stage'))
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    public function nextStage(Order $order): ?LookupValue
    {
        $stages = $this->orderedStages();

        if (! $order->current_stage_lookup_value_id) {
            return $stages->first();
        }

        $currentSort = $stages->firstWhere('id', $order->current_stage_lookup_value_id)?->sort_order ?? -1;

        return $stages->first(fn (LookupValue $stage) => $stage->sort_order > $currentSort);
    }

    public function registerInitial(Order $order, User $actor): void
    {
        $firstStage = $this->orderedStages()->first();

        $this->assert($firstStage !== null, 'No order stages are configured.');

        DB::transaction(function () use ($order, $actor, $firstStage) {
            $order->stageLogs()->create([
                'lookup_value_id' => $firstStage->id,
                'responsible_user_id' => $actor->id,
                'created_by_id' => $actor->id,
                'occurred_at' => now(),
                'is_skipped' => false,
            ]);

            $order->update([
                'current_stage_lookup_value_id' => $firstStage->id,
                'current_stage_since' => now(),
                'is_closed' => $firstStage->code === 'closed',
            ]);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, UploadedFile>  $files
     */
    public function advance(Order $order, LookupValue $target, User $actor, array $data = [], array $files = []): OrderStageLog
    {
        $this->assert(! $order->is_closed, 'This order is already closed.');

        $stages = $this->orderedStages();
        $currentSort = $stages->firstWhere('id', $order->current_stage_lookup_value_id)?->sort_order ?? -1;

        $this->assert($target->sort_order > $currentSort, 'The target stage must be ahead of the current stage.');

        $next = $this->nextStage($order);
        $isJump = ! $next || $target->id !== $next->id;

        if ($isJump) {
            $this->assert($actor->can('orders.advance_any'), 'Jumping ahead of the next stage requires elevated permission.');
        } else {
            $this->assert($actor->can('orders.advance') || $actor->can('orders.advance_any'), 'You are not allowed to advance this order.');
        }

        return DB::transaction(function () use ($order, $target, $actor, $data, $files, $stages, $currentSort) {
            $skippedStages = $stages->filter(
                fn (LookupValue $stage) => $stage->sort_order > $currentSort && $stage->sort_order < $target->sort_order
            );

            foreach ($skippedStages as $stage) {
                $order->stageLogs()->create([
                    'lookup_value_id' => $stage->id,
                    'created_by_id' => $actor->id,
                    'occurred_at' => now(),
                    'is_skipped' => true,
                    'comment' => 'Auto-skipped: order advanced directly to a later stage.',
                ]);
            }

            $log = $order->stageLogs()->create([
                'lookup_value_id' => $target->id,
                'responsible_user_id' => $data['responsible_user_id'] ?? null,
                'created_by_id' => $actor->id,
                'occurred_at' => now(),
                'description' => $data['description'] ?? null,
                'cost' => $data['cost'] ?? null,
                'comment' => $data['comment'] ?? null,
                'is_skipped' => false,
            ]);

            foreach ($files as $file) {
                $path = $file->store("documents/order-stage-log/{$log->id}", 'public');

                $log->documents()->create([
                    'file_path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getClientMimeType(),
                    'size_bytes' => $file->getSize(),
                    'uploaded_by_id' => $actor->id,
                ]);
            }

            $order->update([
                'current_stage_lookup_value_id' => $target->id,
                'current_stage_since' => now(),
                'is_closed' => $target->code === 'closed',
            ]);

            return $log;
        });
    }

    protected function assert(bool $condition, string $message, int $status = 422): void
    {
        if (! $condition) {
            throw new HttpException($status, $message);
        }
    }
}
