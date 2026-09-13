<?php

namespace App\Actions;

use App\Models\ColdStore;
use App\Models\ColdStoreStock;
use App\Models\LoadCrateMovement;
use App\Models\PomegranateLoad;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class MoveLoadToColdStoreAction
{
    public function execute(
        PomegranateLoad $load,
        ColdStore $coldStore,
        int $cratesCount,
        ?float $weightKg = null,
        ?int $recordedBy = null,
        ?string $notes = null,
    ): ColdStoreStock {
        if ($cratesCount < 1) {
            throw new InvalidArgumentException('Crates count must be positive.');
        }

        return DB::transaction(function () use ($load, $coldStore, $cratesCount, $weightKg, $recordedBy, $notes) {
            $load = PomegranateLoad::query()->lockForUpdate()->findOrFail($load->id);
            $coldStore = ColdStore::query()->with('crateStandard')->lockForUpdate()->findOrFail($coldStore->id);

            if (!$coldStore->is_active) {
                throw new RuntimeException('The cold store is closed.');
            }

            $netWeightKg = $weightKg ?? $coldStore->netWeightFor($cratesCount);

            if ($netWeightKg <= 0) {
                throw new InvalidArgumentException('Weight must be positive.');
            }

            if ($cratesCount > $load->on_vehicle_crates_count || $netWeightKg > (float) $load->on_vehicle_weight_kg) {
                throw new RuntimeException('Cannot move more than what remains on the vehicle.');
            }

            $load->on_vehicle_crates_count -= $cratesCount;
            $load->on_vehicle_weight_kg = round((float) $load->on_vehicle_weight_kg - $netWeightKg, 3);
            $load->available_crates_count += $cratesCount;
            $load->available_weight_kg = round((float) $load->available_weight_kg + $netWeightKg, 3);
            $load->refreshStatus();
            $load->save();

            $stock = ColdStoreStock::query()->lockForUpdate()->firstOrCreate(
                ['cold_store_id' => $coldStore->id, 'pomegranate_load_id' => $load->id],
                ['crates_count' => 0, 'weight_kg' => 0]
            );

            $stock->crates_count += $cratesCount;
            $stock->weight_kg = round((float) $stock->weight_kg + $netWeightKg, 3);
            $stock->save();

            $coldStore->current_crates_count += $cratesCount;
            $coldStore->current_weight_kg = round((float) $coldStore->current_weight_kg + $netWeightKg, 3);
            $coldStore->save();

            LoadCrateMovement::create([
                'pomegranate_load_id' => $load->id,
                'cold_store_id' => $coldStore->id,
                'direction' => 'vehicle_to_facility',
                'crates_count' => $cratesCount,
                'weight_kg' => $netWeightKg,
                'reason' => 'cold_store_receiving',
                'moved_at' => now(),
                'recorded_by' => $recordedBy,
                'notes' => $notes,
            ]);

            return $stock;
        });
    }
}
