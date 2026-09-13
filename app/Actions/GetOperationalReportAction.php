<?php

namespace App\Actions;

use App\Models\ColdStore;
use App\Models\CustodyTransaction;
use App\Models\FinishedProduct;
use App\Models\FinishedProductSale;
use App\Models\PomegranateLoad;
use App\Models\PomegranatePurchase;
use App\Models\ProcessingBatch;
use App\Models\SupplierPayment;
use Carbon\CarbonInterface;

class GetOperationalReportAction
{
    public function execute(CarbonInterface $from, CarbonInterface $to): array
    {
        $from = $from->copy()->startOfDay();
        $to = $to->copy()->endOfDay();

        $loads = PomegranateLoad::query()->whereBetween('received_at', [$from, $to]);
        $purchases = PomegranatePurchase::query()->whereBetween('purchased_at', [$from->toDateString(), $to->toDateString()]);
        $supplierPayments = SupplierPayment::query()->whereBetween('paid_at', [$from, $to]);
        $processing = ProcessingBatch::query()->whereBetween('processed_at', [$from, $to]);
        $sales = FinishedProductSale::query()->whereBetween('sold_at', [$from, $to]);

        $processingByType = (clone $processing)
            ->selectRaw('process_type, SUM(input_crates_count) AS input_crates_count, SUM(input_weight_kg) AS input_weight_kg, SUM(output_weight_kg) AS output_weight_kg, SUM(waste_weight_kg) AS waste_weight_kg')
            ->groupBy('process_type')
            ->get()
            ->keyBy('process_type');

        $finishedProducts = FinishedProduct::query()
            ->with('stock')
            ->where('is_active', true)
            ->get()
            ->map(fn (FinishedProduct $product) => [
                'name' => $product->name,
                'code' => $product->code,
                'type' => $product->type,
                'unit' => $product->unit,
                'quantity' => (float) ($product->stock?->quantity ?? 0),
            ])
            ->values()
            ->all();

        $coldStores = ColdStore::query()
            ->where('is_active', true)
            ->get(['id', 'name', 'code', 'current_crates_count', 'current_weight_kg'])
            ->map(function (ColdStore $coldStore) {
                $outstanding = CustodyTransaction::query()
                    ->where('cold_store_id', $coldStore->id)
                    ->selectRaw("SUM(CASE WHEN type = 'issue' THEN crates_count ELSE -crates_count END) AS crates")
                    ->value('crates');

                return [
                    'name' => $coldStore->name,
                    'code' => $coldStore->code,
                    'current_crates_count' => (int) $coldStore->current_crates_count,
                    'current_weight_kg' => (float) $coldStore->current_weight_kg,
                    'custody_outstanding_crates' => max(0, (int) $outstanding),
                ];
            })
            ->values()
            ->all();

        return [
            'period' => [
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
            ],
            'receiving' => [
                'loads_count' => $loads->count(),
                'crates_count' => (int) $loads->sum('loaded_crates_count'),
                'weight_kg' => (float) $loads->sum('loaded_weight_kg'),
            ],
            'purchases' => [
                'count' => $purchases->count(),
                'total_amount' => (float) $purchases->sum('total_amount'),
                'paid_amount' => (float) $purchases->sum('paid_amount'),
                'balance_due' => round((float) $purchases->sum('total_amount') - (float) $purchases->sum('paid_amount'), 3),
            ],
            'supplier_payments' => [
                'count' => $supplierPayments->count(),
                'total_amount' => (float) $supplierPayments->sum('amount'),
            ],
            'processing' => [
                'batches_count' => $processing->count(),
                'input_crates_count' => (int) $processing->sum('input_crates_count'),
                'input_weight_kg' => (float) $processing->sum('input_weight_kg'),
                'output_weight_kg' => (float) $processing->sum('output_weight_kg'),
                'waste_weight_kg' => (float) $processing->sum('waste_weight_kg'),
                'by_type' => $processingByType->map(fn ($row) => [
                    'input_crates_count' => (int) $row->input_crates_count,
                    'input_weight_kg' => (float) $row->input_weight_kg,
                    'output_weight_kg' => (float) $row->output_weight_kg,
                    'waste_weight_kg' => (float) $row->waste_weight_kg,
                ])->all(),
            ],
            'sales' => [
                'count' => $sales->count(),
                'total_amount' => (float) $sales->sum('total_amount'),
                'paid_amount' => (float) $sales->sum('paid_amount'),
                'balance_due' => round((float) $sales->sum('total_amount') - (float) $sales->sum('paid_amount'), 2),
            ],
            'finished_product_stock' => $finishedProducts,
            'cold_stores' => $coldStores,
        ];
    }
}
