<?php

use App\Actions\CreateFinishedProductSaleAction;
use App\Actions\CreatePomegranatePurchaseAction;
use App\Actions\GetOperationalReportAction;
use App\Actions\ProcessPomegranatesAction;
use App\Actions\RecordSupplierPaymentAction;
use App\Models\ColdStore;
use App\Models\FinishedProduct;
use App\Models\FinishedProductStock;
use App\Models\PomegranateLoad;
use App\Models\ProcessingBatch;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('operational report summarizes receiving purchases processing sales and current stock', function () {
    $supplier = Supplier::create(['name' => 'Supplier Report']);
    $vehicle = Vehicle::create(['plate_number' => 'REP-001', 'type' => 'Trailer']);
    $customer = \App\Models\Customer::create(['name' => 'Customer Report']);
    $coldStore = ColdStore::create(['name' => 'براد تقرير', 'code' => 'R-1']);

    $reportDate = Carbon::parse('2026-09-10 10:00:00');
    $load = PomegranateLoad::create([
        'load_number' => 'LOAD-REPORT-001',
        'supplier_id' => $supplier->id,
        'vehicle_id' => $vehicle->id,
        'received_at' => $reportDate,
        'loaded_crates_count' => 100,
        'loaded_weight_kg' => 2000,
        'on_vehicle_crates_count' => 0,
        'on_vehicle_weight_kg' => 0,
        'available_crates_count' => 100,
        'available_weight_kg' => 2000,
        'status' => 'unloaded',
    ]);

    $purchase = app(CreatePomegranatePurchaseAction::class)->execute($load, 'kg', 10, null, 0);
    $purchase->purchased_at = $reportDate->toDateString();
    $purchase->save();
    app(RecordSupplierPaymentAction::class)->execute($purchase, 500, 'cash');
    SupplierPayment::query()->latest('id')->update(['paid_at' => $reportDate]);

    $batch = app(ProcessPomegranatesAction::class)->execute($load, 'peeling', 20, 400, 250, 50);
    $batch->processed_at = $reportDate;
    $batch->save();

    $product = FinishedProduct::query()->where('code', 'peeling')->firstOrFail();
    $sale = app(CreateFinishedProductSaleAction::class)->execute(
        $customer,
        [['finished_product_id' => $product->id, 'quantity' => 50, 'unit_price' => 80]],
        'INV-REPORT-001',
        1000,
    );
    $sale->sold_at = $reportDate;
    $sale->save();

    $summary = app(GetOperationalReportAction::class)->execute(
        Carbon::parse('2026-09-10'),
        Carbon::parse('2026-09-10'),
    );

    expect($summary['receiving']['loads_count'])->toBe(1)
        ->and($summary['receiving']['crates_count'])->toBe(100)
        ->and($summary['receiving']['weight_kg'])->toBe(2000.0)
        ->and($summary['purchases']['count'])->toBe(1)
        ->and($summary['purchases']['paid_amount'])->toBe(500.0)
        ->and($summary['supplier_payments']['total_amount'])->toBe(500.0)
        ->and($summary['processing']['batches_count'])->toBe(1)
        ->and($summary['processing']['input_weight_kg'])->toBe(400.0)
        ->and($summary['processing']['output_weight_kg'])->toBe(250.0)
        ->and($summary['processing']['waste_weight_kg'])->toBe(50.0)
        ->and($summary['processing']['by_type']['peeling']['output_weight_kg'])->toBe(250.0)
        ->and($summary['sales']['count'])->toBe(1)
        ->and($summary['sales']['total_amount'])->toBe(4000.0)
        ->and($summary['sales']['paid_amount'])->toBe(1000.0)
        ->and($summary['sales']['balance_due'])->toBe(3000.0)
        ->and(collect($summary['finished_product_stock'])->firstWhere('code', 'peeling')['quantity'])->toBe(200.0);
});