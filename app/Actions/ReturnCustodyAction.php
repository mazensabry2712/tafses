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

class ReturnCustodyAction
{
    public function execute(
        ColdStore $coldStore,
        PomegranateLoad $load,
        Custodian $custodian,
        int $cratesCount,
        ?float $weightKg = null,
        string $destination = 'vehicle',
        ?int $recordedBy = null,
        ?string $notes = null,
    ): CustodyTransaction {
        if (!in_array($destination, ['vehicle', 'cold_store'], true)) {
            throw new InvalidArgumentException('Return destination must be vehicle or cold_store.');
        }

        if ($cratesCount < 1) {
            throw new InvalidArgumentException('Crates count must be positive.');
        }

        return DB::transaction(function () use ($coldStore, $load, $custodian, $cratesCount, $weightKg, $destination, $recordedBy, $notes) {
            $coldStore = ColdStore::query()->with('crateStandard')->lockForUpdate()->findOrFail($coldStore->id);
            $load = PomegranateLoad::query()->lockForUpdate()->findOrFail($load->id);
            $custodian = Custodian::query()->lockForUpdate()->findOrFail($custodian->id);

            if (!$coldStore->is_active || !$custodian->is_active) {
                throw new RuntimeException('The cold store or custodian is inactive.');
            }

            $issued = (int) CustodyTransaction::query()
                ->where('custodian_id', $custodian->id)
                ->where('pomegranate_load_id', $load->id)
                ->where('cold_store_id', $coldStore->id)
                ->where('type', 'issue')
                ->sum('crates_count');

            $returned = (int) CustodyTransaction::query()
                ->where('custodian_id', $custodian->id)
                ->where('pomegranate_load_id', $load->id)
                ->where('cold_store_id', $coldStore->id)
                ->whereIn('type', ['return_to_vehicle', 'return_to_store'])
                ->sum('crates_count');

            $outstandingCrates = $issued - $returned;
            $issuedWeight = (float) CustodyTransaction::query()
                ->where('custodian_id', $custodian->id)
                ->where('pomegranate_load_id', $load->id)
                ->where('cold_store_id', $coldStore->id)
                ->where('type', 'issue')
                ->sum('weight_kg');
            $returnedWeight = (float) CustodyTransaction::query()
                ->where('custodian_id', $custodian->id)
                ->where('pomegranate_load_id', $load->id)
                ->where('cold_store_id', $coldStore->id)
                ->whereIn('type', ['return_to_vehicle', 'return_to_store'])
                ->sum('weight_kg');
            $outstandingWeight = round($issuedWeight - $returnedWeight, 3);

            $netWeightKg = $weightKg ?? $coldStore->netWeightFor($cratesCount);

            if ($netWeightKg <= 0 || $cratesCount > $outstandingCrates || $netWeightKg > $outstandingWeight + 0.0005) {
                throw new RuntimeException('Cannot return more than the custodian currently holds.');
            }

            if ($destination === 'vehicle') {
                $load->on_vehicle_crates_count += $cratesCount;
                $load->on_vehicle_weight_kg = round((float) $load->on_vehicle_weight_kg + $netWeightKg, 3);
                $load->save();
            } else {
                $stock = ColdStoreStock::query()
                    ->where('cold_store_id', $coldStore->id)
                    ->where('pomegranate_load_id', $load->id)
                    ->lockForUpdate()
                    ->firstOrCreate(
                        ['cold_store_id' => $coldStore->id, 'pomegranate_load_id' => $load->id],
                        ['crates_count' => 0, 'weight_kg' => 0]
                    );

                $stock->crates_count += $cratesCount;
                $stock->weight_kg = round((float) $stock->weight_kg + $netWeightKg, 3);
                $stock->save();

                $coldStore->current_crates_count += $cratesCount;
                $coldStore->current_weight_kg = round((float) $coldStore->current_weight_kg + $netWeightKg, 3);
                $coldStore->save();

                $load->available_crates_count += $cratesCount;
                $load->available_weight_kg = round((float) $load->available_weight_kg + $netWeightKg, 3);
                $load->save();
            }

            return CustodyTransaction::create([
                'custodian_id' => $custodian->id,
                'cold_store_id' => $coldStore->id,
                'pomegranate_load_id' => $load->id,
                'type' => $destination === 'vehicle' ? 'return_to_vehicle' : 'return_to_store',
                'crates_count' => $cratesCount,
                'weight_kg' => $netWeightKg,
                'moved_at' => now(),
                'recorded_by' => $recordedBy,
                'notes' => $notes,
            ]);
        });
    }
}
