<?php

namespace App\Http\Controllers;

use App\Actions\ProcessPomegranatesAction;
use App\Models\PomegranateLoad;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProcessingController extends Controller
{
    public function store(Request $request, PomegranateLoad $load, ProcessPomegranatesAction $action): RedirectResponse
    {
        $data = $request->validate([
            'process_type' => ['required', 'in:peeling,juice'],
            'input_crates_count' => ['required', 'integer', 'min:1'],
            'input_weight_kg' => ['required', 'numeric', 'gt:0'],
            'output_weight_kg' => ['required', 'numeric', 'gte:0'],
            'waste_weight_kg' => ['nullable', 'numeric', 'gte:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $action->execute(
            $load,
            $data['process_type'],
            (int) $data['input_crates_count'],
            (float) $data['input_weight_kg'],
            (float) $data['output_weight_kg'],
            (float) ($data['waste_weight_kg'] ?? 0),
            $request->user()->id,
            $data['notes'] ?? null,
        );

        return back()->with('success', 'Processing batch recorded successfully.');
    }
}
