<?php

namespace App\Services\Warehouse;

use App\Models\Products\Product;
use App\Models\User;
use App\Models\Warehouse\StockMovement;
use App\Models\Warehouse\Warehouse;
use App\Models\Warehouse\WarehouseStockBalance;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;

class StockService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function recordMovement(
        Warehouse $warehouse,
        Product $product,
        string $type,
        float $quantity,
        User $actor,
        array $data = []
    ): StockMovement {
        $this->assert(in_array($type, ['in', 'out', 'adjustment'], true), 'Invalid stock movement type.');
        $this->assert($quantity !== 0.0, 'Quantity must not be zero.');

        $delta = match ($type) {
            'in' => abs($quantity),
            'out' => -abs($quantity),
            'adjustment' => $quantity,
        };

        return DB::transaction(function () use ($warehouse, $product, $type, $quantity, $actor, $data, $delta) {
            $balance = WarehouseStockBalance::firstOrCreate(
                ['warehouse_id' => $warehouse->id, 'product_id' => $product->id],
                ['quantity' => 0]
            );

            $newBalance = (float) $balance->quantity + $delta;

            $this->assert($newBalance >= 0, 'This movement would leave the stock balance negative.');

            $balance->update(['quantity' => $newBalance]);

            return StockMovement::create([
                'warehouse_id' => $warehouse->id,
                'product_id' => $product->id,
                'type' => $type,
                'quantity' => abs($quantity),
                'unit_cost' => $data['unit_cost'] ?? null,
                'business_partner_id' => $data['business_partner_id'] ?? null,
                'order_id' => $data['order_id'] ?? null,
                'reference' => $data['reference'] ?? null,
                'note' => $data['note'] ?? null,
                'occurred_at' => now(),
                'created_by_id' => $actor->id,
            ]);
        });
    }

    public function balanceFor(Product $product, ?Warehouse $warehouse = null): float
    {
        $query = WarehouseStockBalance::where('product_id', $product->id);

        if ($warehouse) {
            $query->where('warehouse_id', $warehouse->id);
        }

        return (float) $query->sum('quantity');
    }

    protected function assert(bool $condition, string $message, int $status = 422): void
    {
        if (! $condition) {
            throw new HttpException($status, $message);
        }
    }
}
