<?php

namespace App\Actions;

use App\Models\FinishedProduct;
use App\Models\FinishedProductStock;
use App\Models\StockAdjustment;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class CreateFinishedProductStockAdjustmentAction
{
    public function execute(
        FinishedProduct $product,
        string $direction,
        float $quantity,
        string $reason,
        ?int $recordedBy = null,
        ?string $adjustedAt = null,
    ): StockAdjustment {
        if (!in_array($direction, ['increase', 'decrease'], true)) {
            throw new InvalidArgumentException('Adjustment direction must be increase or decrease.');
        }

        if ($quantity <= 0) {
            throw new InvalidArgumentException('Adjustment quantity must be greater than zero.');
        }

        $reason = trim($reason);
        if ($reason === '') {
            throw new InvalidArgumentException('Adjustment reason is required.');
        }

        return DB::transaction(function () use ($product, $direction, $quantity, $reason, $recordedBy, $adjustedAt) {
            $product = FinishedProduct::query()->lockForUpdate()->findOrFail($product->id);

            if (!$product->is_active) {
                throw new RuntimeException('The finished product is inactive.');
            }

            $stock = FinishedProductStock::query()
                ->where('finished_product_id', $product->id)
                ->lockForUpdate()
                ->first();

            $current = (float) ($stock?->quantity ?? 0);
            $delta = $direction === 'increase' ? $quantity : -$quantity;
            $newQuantity = round($current + $delta, 3);

            if ($newQuantity < 0) {
                throw new RuntimeException('Stock adjustment cannot make finished product stock negative.');
            }

            if (!$stock) {
                $stock = FinishedProductStock::create([
                    'finished_product_id' => $product->id,
                    'quantity' => 0,
                ]);
            }

            $stock->quantity = $newQuantity;
            $stock->save();

            $adjustment = StockAdjustment::create([
                'finished_product_id' => $product->id,
                'direction' => $direction,
                'quantity' => $quantity,
                'reason' => $reason,
                'adjusted_at' => $adjustedAt ? now()->parse($adjustedAt) : now(),
                'recorded_by' => $recordedBy,
            ]);

            $product->transactions()->create([
                'type' => $direction === 'increase' ? 'adjustment_in' : 'adjustment_out',
                'quantity' => $delta,
                'moved_at' => $adjustment->adjusted_at,
                'recorded_by' => $recordedBy,
                'notes' => "Stock adjustment #{$adjustment->id}: {$reason}",
            ]);

            return $adjustment->load('product');
        });
    }
}
