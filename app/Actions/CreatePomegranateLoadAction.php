<?php

namespace App\Actions;

use App\Models\PomegranateLoad;
use Illuminate\Support\Carbon;
use InvalidArgumentException;

class CreatePomegranateLoadAction
{
    public function execute(
        string $loadNumber,
        ?int $supplierId,
        ?int $vehicleId,
        int $cratesCount,
        float $weightKg,
        ?Carbon $receivedAt = null,
        ?string $notes = null,
    ): PomegranateLoad {
        if (trim($loadNumber) === '') {
            throw new InvalidArgumentException('Load number is required.');
        }

        if ($cratesCount < 1 || $weightKg <= 0) {
            throw new InvalidArgumentException('Crates count and weight must be positive.');
        }

        return PomegranateLoad::create([
            'load_number' => trim($loadNumber),
            'supplier_id' => $supplierId,
            'vehicle_id' => $vehicleId,
            'received_at' => $receivedAt ?? now(),
            'loaded_crates_count' => $cratesCount,
            'loaded_weight_kg' => $weightKg,
            'on_vehicle_crates_count' => $cratesCount,
            'on_vehicle_weight_kg' => $weightKg,
            'available_crates_count' => 0,
            'available_weight_kg' => 0,
            'status' => 'open',
            'notes' => $notes,
        ]);
    }
}
