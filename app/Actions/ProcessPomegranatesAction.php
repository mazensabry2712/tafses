<?php

namespace App\Actions;

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

        return DB::transaction(function () use ($load, $processType, $inputCratesCount, $inputWeightKg, $outputWeightKg, $wasteWeightKg, $recordedBy, $notes) {
            $load = PomegranateLoad::query()->lockForUpdate()->findOrFail($load->id);

            if ($inputCratesCount > $load->available_crates_count || $inputWeightKg > (float) $load->available_weight_kg) {
                throw new RuntimeException('Cannot process more crates or weight than what is currently available.');
            }

            $load->available_crates_count -= $inputCratesCount;
            $load->available_weight_kg = (float) $load->available_weight_kg - $inputWeightKg;
            $load->save();

            return $load->processingBatches()->create([
                'process_type' => $processType,
                'input_crates_count' => $inputCratesCount,
                'input_weight_kg' => $inputWeightKg,
                'output_weight_kg' => $outputWeightKg,
                'waste_weight_kg' => $wasteWeightKg,
                'processed_at' => now(),
                'recorded_by' => $recordedBy,
                'notes' => $notes,
            ]);
        });
    }
}
