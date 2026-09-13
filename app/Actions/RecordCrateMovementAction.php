<?php

namespace App\Actions;

use App\Models\LoadCrateMovement;
use App\Models\PomegranateLoad;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class RecordCrateMovementAction
{
    public function execute(
        PomegranateLoad $load,
        string $direction,
        int $cratesCount,
        float $weightKg,
        string $reason = 'unloading',
        ?int $recordedBy = null,
        ?string $notes = null,
    ): LoadCrateMovement {
        if (!in_array($direction, ['vehicle_to_facility', 'facility_to_vehicle'], true)) {
            throw new InvalidArgumentException('Invalid crate movement direction.');
        }

        if ($cratesCount < 1 || $weightKg <= 0) {
            throw new InvalidArgumentException('Crates count and weight must be positive.');
        }

        return DB::transaction(function () use ($load, $direction, $cratesCount, $weightKg, $reason, $recordedBy, $notes) {
            $load = PomegranateLoad::query()->lockForUpdate()->findOrFail($load->id);

            if ($direction === 'vehicle_to_facility') {
                if ($cratesCount > $load->on_vehicle_crates_count || $weightKg > (float) $load->on_vehicle_weight_kg) {
                    throw new RuntimeException('Cannot unload more crates or weight than what remains on the vehicle.');
                }

                $load->on_vehicle_crates_count -= $cratesCount;
                $load->on_vehicle_weight_kg = (float) $load->on_vehicle_weight_kg - $weightKg;
                $load->available_crates_count += $cratesCount;
                $load->available_weight_kg = (float) $load->available_weight_kg + $weightKg;
            } else {
                if ($cratesCount > $load->available_crates_count || $weightKg > (float) $load->available_weight_kg) {
                    throw new RuntimeException('Cannot return more crates or weight than what is available at the facility.');
                }

                $load->available_crates_count -= $cratesCount;
                $load->available_weight_kg = (float) $load->available_weight_kg - $weightKg;
                $load->on_vehicle_crates_count += $cratesCount;
                $load->on_vehicle_weight_kg = (float) $load->on_vehicle_weight_kg + $weightKg;
            }

            $load->refreshStatus();
            $load->save();

            return $load->crateMovements()->create([
                'direction' => $direction,
                'crates_count' => $cratesCount,
                'weight_kg' => $weightKg,
                'reason' => $reason,
                'moved_at' => now(),
                'recorded_by' => $recordedBy,
                'notes' => $notes,
            ]);
        });
    }
}
