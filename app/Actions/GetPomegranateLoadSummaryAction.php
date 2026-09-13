<?php

namespace App\Actions;

use App\Models\PomegranateLoad;

class GetPomegranateLoadSummaryAction
{
    public function execute(PomegranateLoad $load): array
    {
        $load->load(['supplier', 'vehicle']);

        $processing = $load->processingBatches()
            ->selectRaw("SUM(input_crates_count) as input_crates_count, SUM(input_weight_kg) as input_weight_kg, SUM(output_weight_kg) as output_weight_kg, SUM(waste_weight_kg) as waste_weight_kg")
            ->first();

        $peelingOutput = (float) $load->processingBatches()
            ->where('process_type', 'peeling')
            ->sum('output_weight_kg');

        $juiceOutput = (float) $load->processingBatches()
            ->where('process_type', 'juice')
            ->sum('output_weight_kg');

        return [
            'load_number' => $load->load_number,
            'status' => $load->status,
            'supplier' => $load->supplier?->name,
            'vehicle' => $load->vehicle?->plate_number,
            'loaded' => [
                'crates_count' => $load->loaded_crates_count,
                'weight_kg' => (float) $load->loaded_weight_kg,
            ],
            'on_vehicle' => [
                'crates_count' => $load->on_vehicle_crates_count,
                'weight_kg' => (float) $load->on_vehicle_weight_kg,
            ],
            'available_for_processing' => [
                'crates_count' => $load->available_crates_count,
                'weight_kg' => (float) $load->available_weight_kg,
            ],
            'processing' => [
                'input_crates_count' => (int) ($processing->input_crates_count ?? 0),
                'input_weight_kg' => (float) ($processing->input_weight_kg ?? 0),
                'peeling_output_weight_kg' => $peelingOutput,
                'juice_output_weight_kg' => $juiceOutput,
                'waste_weight_kg' => (float) ($processing->waste_weight_kg ?? 0),
            ],
        ];
    }
}
