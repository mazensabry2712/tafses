<?php

namespace App\Actions;

use App\Models\ColdStore;
use App\Models\ColdStoreStock;
use App\Models\FinishedProduct;
use App\Models\FinishedProductStock;
use App\Models\PomegranateLoad;
use App\Models\ProcessingBatch;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class ProcessPomegranatesAction
{
    public function execute(
        PomegranateLoad $load,
        string $processType,
        int $inputCratesCount,
        float $inputWeightKg,
        float $outputWeightKg,
        float $wasteWeightKg = 0,
        ?int $recordedBy = null,
        ?string $notes = null,
        ?ColdStore $coldStore = null,
    ): ProcessingBatch {
        if (!in_array($processType, ['peeling', 'juice'], true)) {
            throw new InvalidArgumentException('Process type must be peeling or juice.');
        }

        if ($inputCratesCount < 1 || $inputWeightKg <= 0 || $outputWeightKg < 0 || $wasteWeightKg < 0) {
            throw new InvalidArgumentException('Processing quantities are invalid.');
        }

        if (($outputWeightKg + $wasteWeightKg) > ($inputWeightKg + 0.0005)) {
            throw new InvalidArgumentException('Output plus waste cannot exceed input weight.');
        }

        return DB::transaction(function () use ($load, $processType, $inputCratesCount, $inputWeightKg, $outputWeightKg, $wasteWeightKg, $recordedBy, $notes, $coldStore) {
            $load = PomegranateLoad::query()->lockForUpdate()->findOrFail($load->id);

            $coldStoreStock = null;

            if ($coldStore) {
                $coldStore = ColdStore::query()->lockForUpdate()->findOrFail($coldStore->id);

                if (!$coldStore->is_active) {
                    throw new RuntimeException('The cold store is closed.');
                }

                $coldStoreStock = ColdStoreStock::query()
                    ->where('cold_store_id', $coldStore->id)
                    ->where('pomegranate_load_id', $load->id)
                    ->lockForUpdate()
                    ->first();

                if (!$coldStoreStock) {
                    throw new RuntimeException('This load has no stock in the selected cold store.');
                }

                if ($inputCratesCount > $coldStoreStock->crates_count || $inputWeightKg > (float) $coldStoreStock->weight_kg) {
                    throw new RuntimeException('Cannot process more than the selected cold store stock.');
                }
            }

            if ($inputCratesCount > $load->available_crates_count || $inputWeightKg > (float) $load->available_weight_kg) {
                throw new RuntimeException('Cannot process more crates or weight than what is currently available.');
            }

            $product = FinishedProduct::query()->firstOrCreate(
                ['code' => $processType],
                [
                    'name' => $processType === 'peeling' ? 'Pomegranate Arils' : 'Pomegranate Juice',
                    'type' => $processType,
                    'unit' => 'kg',
                    'is_active' => true,
                ]
            );

            if (!$product->is_active) {
                throw new RuntimeException('The finished product is inactive.');
            }

            $load->available_crates_count -= $inputCratesCount;
            $load->available_weight_kg = round((float) $load->available_weight_kg - $inputWeightKg, 3);
            $load->save();

            if ($coldStore && $coldStoreStock) {
                $coldStoreStock->crates_count -= $inputCratesCount;
                $coldStoreStock->weight_kg = round((float) $coldStoreStock->weight_kg - $inputWeightKg, 3);
                $coldStoreStock->save();

                $coldStore->current_crates_count -= $inputCratesCount;
                $coldStore->current_weight_kg = round((float) $coldStore->current_weight_kg - $inputWeightKg, 3);
                $coldStore->save();
            }

            $batch = $load->processingBatches()->create([
                'process_type' => $processType,
                'input_crates_count' => $inputCratesCount,
                'input_weight_kg' => $inputWeightKg,
                'output_weight_kg' => $outputWeightKg,
                'waste_weight_kg' => $wasteWeightKg,
                'processed_at' => now(),
                'recorded_by' => $recordedBy,
                'notes' => $notes,
            ]);

            if ($coldStore) {
                $batch->cold_store_id = $coldStore->id;
                $batch->save();
            }

            if ($outputWeightKg > 0) {
                $stock = FinishedProductStock::query()
                    ->where('finished_product_id', $product->id)
                    ->lockForUpdate()
                    ->firstOrCreate(
                        ['finished_product_id' => $product->id],
                        ['quantity' => 0]
                    );

                $stock->quantity = round((float) $stock->quantity + $outputWeightKg, 3);
                $stock->save();

                $product->transactions()->create([
                    'processing_batch_id' => $batch->id,
                    'type' => 'production',
                    'quantity' => $outputWeightKg,
                    'moved_at' => now(),
                    'recorded_by' => $recordedBy,
                    'notes' => $notes,
                ]);
            }

            return $batch;
        });
    }
}
