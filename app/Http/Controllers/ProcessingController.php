<?php

namespace App\Http\Controllers;

use App\Actions\ProcessPomegranatesAction;
use App\Models\ColdStore;
use App\Models\FinishedProduct;
use App\Models\PomegranateLoad;
use App\Models\ProcessingBatch;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProcessingController extends Controller
{
    public function index(): View
    {
        $loads = PomegranateLoad::query()
            ->with([
                'supplier',
                'coldStoreStocks.coldStore' => fn ($query) => $query->where('is_active', true),
            ])
            ->where('available_crates_count', '>', 0)
            ->where('available_weight_kg', '>', 0)
            ->orderByDesc('received_at')
            ->get();

        $products = FinishedProduct::query()
            ->with('stock')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $recentBatches = ProcessingBatch::query()
            ->with(['pomegranateLoad', 'recorder'])
            ->orderByDesc('processed_at')
            ->orderByDesc('id')
            ->limit(30)
            ->get();

        $processingSummary = [
            'batches_count' => $recentBatches->count(),
            'input_weight_kg' => (float) $recentBatches->sum(fn ($batch) => (float) $batch->input_weight_kg),
            'output_weight_kg' => (float) $recentBatches->sum(fn ($batch) => (float) $batch->output_weight_kg),
            'waste_weight_kg' => (float) $recentBatches->sum(fn ($batch) => (float) $batch->waste_weight_kg),
        ];

        return view('processing.index', [
            'loads' => $loads,
            'coldStores' => ColdStore::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
            'products' => $products->filter(fn (FinishedProduct $product) => (float) ($product->stock?->quantity ?? 0) > 0)->values(),
            'recentBatches' => $recentBatches,
            'processingSummary' => $processingSummary,
        ]);
    }

    public function store(Request $request, PomegranateLoad $load, ProcessPomegranatesAction $action): RedirectResponse
    {
        $data = $request->validate([
            'cold_store_id' => ['required', 'integer', 'exists:cold_stores,id'],
            'process_type' => ['required', 'in:peeling,juice'],
            'input_crates_count' => ['required', 'integer', 'min:1'],
            'input_weight_kg' => ['required', 'numeric', 'gt:0'],
            'output_weight_kg' => ['required', 'numeric', 'gte:0'],
            'waste_weight_kg' => ['nullable', 'numeric', 'gte:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $coldStore = ColdStore::query()->findOrFail($data['cold_store_id']);

        $action->execute(
            $load,
            $data['process_type'],
            (int) $data['input_crates_count'],
            (float) $data['input_weight_kg'],
            (float) $data['output_weight_kg'],
            (float) ($data['waste_weight_kg'] ?? 0),
            $request->user()->id,
            $data['notes'] ?? null,
            $coldStore,
        );

        return back()->with('success', 'تم تسجيل دفعة التصنيع بنجاح.');
    }
}
