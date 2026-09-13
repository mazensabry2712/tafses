<?php

use App\Actions\CreatePomegranateLoadAction;
use App\Actions\CreatePomegranatePurchaseAction;
use App\Actions\GetPomegranateLoadSummaryAction;
use App\Actions\GetSupplierBalanceAction;
use App\Actions\ProcessPomegranatesAction;
use App\Actions\RecordCrateMovementAction;
use App\Actions\RecordSupplierPaymentAction;
use App\Models\PomegranateLoad;
use App\Models\Supplier;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeLoad(array $overrides = []): PomegranateLoad
{
    $supplier = Supplier::create(['name' => 'Test Supplier']);
    $vehicle = Vehicle::create(['plate_number' => 'TEST-123', 'type' => 'Trailer']);

    return PomegranateLoad::create(array_merge([
        'load_number' => 'LOAD-TEST-001',
        'supplier_id' => $supplier->id,
        'vehicle_id' => $vehicle->id,
        'received_at' => now(),
        'loaded_crates_count' => 100,
        'loaded_weight_kg' => 2000,
        'on_vehicle_crates_count' => 100,
        'on_vehicle_weight_kg' => 2000,
        'available_crates_count' => 0,
        'available_weight_kg' => 0,
        'status' => 'open',
    ], $overrides));
}

test('a pomegranate load starts fully on the vehicle', function () {
    $supplier = Supplier::create(['name' => 'Farmer One']);
    $vehicle = Vehicle::create(['plate_number' => 'AAA-111', 'type' => 'Truck']);

    $load = app(CreatePomegranateLoadAction::class)->execute(
        'LOAD-2026-0001',
        $supplier->id,
        $vehicle->id,
        420,
        8400,
    );

    expect($load->load_number)->toBe('LOAD-2026-0001')
        ->and($load->loaded_crates_count)->toBe(420)
        ->and((float) $load->loaded_weight_kg)->toBe(8400.0)
        ->and($load->on_vehicle_crates_count)->toBe(420)
        ->and((float) $load->on_vehicle_weight_kg)->toBe(8400.0)
        ->and($load->available_crates_count)->toBe(0)
        ->and((float) $load->available_weight_kg)->toBe(0.0)
        ->and($load->status)->toBe('open');
});

test('crates can move from vehicle to facility and back with an audit record', function () {
    $load = makeLoad();
    $action = app(RecordCrateMovementAction::class);

    $movement = $action->execute($load, 'vehicle_to_facility', 30, 600);
    $load->refresh();

    expect($movement->direction)->toBe('vehicle_to_facility')
        ->and($load->on_vehicle_crates_count)->toBe(70)
        ->and((float) $load->on_vehicle_weight_kg)->toBe(1400.0)
        ->and($load->available_crates_count)->toBe(30)
        ->and((float) $load->available_weight_kg)->toBe(600.0);

    $action->execute($load, 'facility_to_vehicle', 5, 100, 'return');
    $load->refresh();

    expect($load->on_vehicle_crates_count)->toBe(75)
        ->and((float) $load->on_vehicle_weight_kg)->toBe(1500.0)
        ->and($load->available_crates_count)->toBe(25)
        ->and((float) $load->available_weight_kg)->toBe(500.0)
        ->and($load->crateMovements()->count())->toBe(2);
});

test('a movement cannot exceed the available quantity', function () {
    $load = makeLoad(['on_vehicle_crates_count' => 10, 'on_vehicle_weight_kg' => 200]);

    expect(fn () => app(RecordCrateMovementAction::class)->execute($load, 'vehicle_to_facility', 11, 200))
        ->toThrow(RuntimeException::class);
});

test('pomegranates can be processed for peeling and reduce available stock', function () {
    $load = makeLoad([
        'on_vehicle_crates_count' => 0,
        'on_vehicle_weight_kg' => 0,
        'available_crates_count' => 100,
        'available_weight_kg' => 2000,
        'status' => 'unloaded',
    ]);

    $batch = app(ProcessPomegranatesAction::class)->execute($load, 'peeling', 20, 400, 250, 50);
    $load->refresh();

    expect($batch->process_type)->toBe('peeling')
        ->and((float) $batch->output_weight_kg)->toBe(250.0)
        ->and((float) $batch->waste_weight_kg)->toBe(50.0)
        ->and($load->available_crates_count)->toBe(80)
        ->and((float) $load->available_weight_kg)->toBe(1600.0);
});

test('a load summary reports peeling juice and waste outputs', function () {
    $load = makeLoad([
        'on_vehicle_crates_count' => 0,
        'on_vehicle_weight_kg' => 0,
        'available_crates_count' => 100,
        'available_weight_kg' => 2000,
        'status' => 'unloaded',
    ]);

    $processor = app(ProcessPomegranatesAction::class);
    $processor->execute($load, 'peeling', 30, 600, 360, 90);
    $processor->execute($load, 'juice', 20, 400, 280, 40);

    $summary = app(GetPomegranateLoadSummaryAction::class)->execute($load->fresh());

    expect($summary['loaded']['crates_count'])->toBe(100)
        ->and($summary['available_for_processing']['crates_count'])->toBe(50)
        ->and($summary['available_for_processing']['weight_kg'])->toBe(1000.0)
        ->and($summary['processing']['input_crates_count'])->toBe(50)
        ->and($summary['processing']['input_weight_kg'])->toBe(1000.0)
        ->and($summary['processing']['peeling_output_weight_kg'])->toBe(360.0)
        ->and($summary['processing']['juice_output_weight_kg'])->toBe(280.0)
        ->and($summary['processing']['waste_weight_kg'])->toBe(130.0);
});

test('a load can be purchased by weight and the supplier balance decreases with payment', function () {
    $load = makeLoad();

    $purchase = app(CreatePomegranatePurchaseAction::class)->execute(
        $load,
        'kg',
        15.5,
        null,
        10000,
    );

    expect((float) $purchase->total_amount)->toBe(31000.0)
        ->and((float) $purchase->paid_amount)->toBe(10000.0)
        ->and($purchase->balance())->toBe(21000.0);

    app(RecordSupplierPaymentAction::class)->execute($purchase, 5000, 'cash');

    $balance = app(GetSupplierBalanceAction::class)->execute($purchase->supplier->fresh());

    expect($balance['purchase_total'])->toBe(31000.0)
        ->and($balance['paid_total'])->toBe(15000.0)
        ->and($balance['balance_due'])->toBe(16000.0);
});

test('a supplier payment cannot exceed the remaining purchase balance', function () {
    $load = makeLoad();
    $purchase = app(CreatePomegranatePurchaseAction::class)->execute($load, 'crate', 100, null, 0);

    expect(fn () => app(RecordSupplierPaymentAction::class)->execute($purchase, 10100))
        ->toThrow(InvalidArgumentException::class);
});

test('processing cannot consume more stock than is available', function () {
    $load = makeLoad([
        'on_vehicle_crates_count' => 0,
        'on_vehicle_weight_kg' => 0,
        'available_crates_count' => 10,
        'available_weight_kg' => 200,
        'status' => 'unloaded',
    ]);

    expect(fn () => app(ProcessPomegranatesAction::class)->execute($load, 'juice', 11, 200, 150, 20))
        ->toThrow(RuntimeException::class);
});

test('processing cannot report output and waste above the input weight', function () {
    $load = makeLoad([
        'on_vehicle_crates_count' => 0,
        'on_vehicle_weight_kg' => 0,
        'available_crates_count' => 10,
        'available_weight_kg' => 200,
        'status' => 'unloaded',
    ]);

    expect(fn () => app(ProcessPomegranatesAction::class)->execute($load, 'juice', 1, 100, 90, 20))
        ->toThrow(InvalidArgumentException::class);
});
