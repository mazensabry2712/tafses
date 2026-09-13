<?php

namespace App\Actions;

use App\Models\ColdStore;
use App\Models\ColdStoreStock;
use App\Models\Custodian;
use App\Models\CustodyTransaction;
use App\Models\PomegranateLoad;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class IssueCratesToCustodianAction
{
    public function execute(
        ColdStore $coldStore,
        PomegranateLoad $load,
        Custodian $custodian,
        int $cratesCount,
        float $weightKg,
        ?int $recordedBy = null,
        ?string $notes = null,
    ): CustodyTransaction {
        if ($cratesCount < 1 || $weightKg <= 0) {
            throw new InvalidArgumentException('Crates count and weight must be positive.');
        }

        return DB::transaction(function () use ($coldStore, $load, $custodian, $cratesCount, $weightKg, $recordedBy, $notes) {
            $coldStore = ColdStore::query()->lockForUpdate()->findOrFail($coldStore->id);
            $load = PomegranateLoad::query()->lockForUpdate()->findOrFail($load->id);
            $custodian = Custodian::query()->lockForUpdate()->findOrFail($custodian->id);

            if (!$coldStore->is_active || !$custodian->is_active) {
                throw new RuntimeException('The cold store or custodian is inactive.');
            }

            $stock = ColdStoreStock::query()
                ->where('cold_store_id', $coldStore->id)
                ->where('pomegranate_load_id', $load->id)
                ->lockForUpdate()
                ->first();

            if (!$stock) {
                throw new RuntimeException('This load has no stock in the selected cold store.');
            }

            if ($cratesCount > $stock->crates_count || $weightKg > (float) $stock->weight_kg) {
                throw new RuntimeException('Cannot issue more than the stock available in the cold store for this load.');
            }

            $stock->crates_count -= $cratesCount;
            $stock->weight_kg = round((float) $stock->weight_kg - $weightKg, 3);
            $stock->save();

            $coldStore->current_crates_count -= $cratesCount;
            $coldStore->current_weight_kg = round((float) $coldStore->current_weight_kg - $weightKg, 3);
            $coldStore->save();

            $load->available_crates_count -= $cratesCount;
            $load->available_weight_kg = round((float) $load->available_weight_kg - $weightKg, 3);
            $load->save();

            return CustodyTransaction::create([
                'custodian_id' => $custodian->id,
                'cold_store_id' => $coldStore->id,
                'pomegranate_load_id' => $load->id,
                'type' => 'issue',
                'crates_count' => $cratesCount,
                'weight_kg' => $weightKg,
                'moved_at' => now(),
                'recorded_by' => $recordedBy,
                'notes' => $notes,
            ]);
        });
    }
}
