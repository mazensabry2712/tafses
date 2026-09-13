<?php

use App\Actions\ProcessPomegranatesAction;
use App\Actions\RecordCrateMovementAction;
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
