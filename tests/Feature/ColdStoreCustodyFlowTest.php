<?php

use App\Actions\CloseColdStoreAction;
use App\Actions\GetCustodySummaryAction;
use App\Actions\IssueCratesToCustodianAction;
use App\Actions\MoveLoadToColdStoreAction;
use App\Actions\ReturnCustodyAction;
use App\Models\ColdStore;
use App\Models\CrateStandard;
use App\Models\Custodian;
use App\Models\PomegranateLoad;
use App\Models\Supplier;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('a custodian can receive multiple open issues and returns reduce outstanding while vehicle gets returned crates', function () {
    $supplier = Supplier::create(['name' => 'Supplier One']);
    $vehicle = Vehicle::create(['plate_number' => 'AAA-111', 'type' => 'Trailer']);
    $standard = CrateStandard::create(['name' => '25kg crate', 'gross_weight_kg' => 25, 'tare_weight_kg' => 2.5]);
    $coldStore = ColdStore::create(['name' => 'براد 1', 'code' => 'BR-1', 'crate_standard_id' => $standard->id]);
    $mahmoud = Custodian::create(['name' => 'Mahmoud']);

    $load = PomegranateLoad::create([
        'load_number' => 'LOAD-CUSTODY-001',
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
    ]);

    app(MoveLoadToColdStoreAction::class)->execute($load, $coldStore, 40, 800);

    $issuer = app(IssueCratesToCustodianAction::class);
    $issuer->execute($coldStore, $load, $mahmoud, 21, 420);
    $issuer->execute($coldStore, $load, $mahmoud, 3, 60);
    $issuer->execute($coldStore, $load, $mahmoud, 8, 160);

    $summary = app(GetCustodySummaryAction::class)->execute($coldStore->fresh());
    $mahmoudSummary = $summary['custodians'][0];

    expect($mahmoudSummary['issued_crates'])->toBe(32)
        ->and($mahmoudSummary['returned_crates'])->toBe(0)
        ->and($mahmoudSummary['outstanding_crates'])->toBe(32)
        ->and($summary['current']['crates_count'])->toBe(8);

    app(ReturnCustodyAction::class)->execute($coldStore, $load, $mahmoud, 12, 240, 'vehicle');
    app(IssueCratesToCustodianAction::class)->execute($coldStore, $load, $mahmoud, 5, 100);

    $summary = app(GetCustodySummaryAction::class)->execute($coldStore->fresh());
    $mahmoudSummary = $summary['custodians'][0];
    $load->refresh();

    expect($mahmoudSummary['issued_crates'])->toBe(37)
        ->and($mahmoudSummary['returned_crates'])->toBe(12)
        ->and($mahmoudSummary['outstanding_crates'])->toBe(25)
        ->and($mahmoudSummary['issued_weight_kg'])->toBe(740.0)
        ->and($mahmoudSummary['returned_weight_kg'])->toBe(240.0)
        ->and($mahmoudSummary['outstanding_weight_kg'])->toBe(500.0)
        ->and($summary['current']['crates_count'])->toBe(3)
        ->and((float) $summary['current']['weight_kg'])->toBe(60.0)
        ->and($load->on_vehicle_crates_count)->toBe(72)
        ->and((float) $load->on_vehicle_weight_kg)->toBe(1440.0);
});

test('standard crate weight calculates 25kg gross as 22.5kg net', function () {
    $standard = CrateStandard::create([
        'name' => '25kg crate',
        'gross_weight_kg' => 25,
        'tare_weight_kg' => 2.5,
    ]);

    expect($standard->netWeightKg())->toBe(22.5)
        ->and($standard->netWeightFor(21))->toBe(472.5);
});

test('crate issue can calculate net weight automatically from the cold store standard', function () {
    $supplier = Supplier::create(['name' => 'Supplier Two']);
    $vehicle = Vehicle::create(['plate_number' => 'CCC-333', 'type' => 'Trailer']);
    $standard = CrateStandard::create(['name' => '25kg crate', 'gross_weight_kg' => 25, 'tare_weight_kg' => 2.5]);
    $coldStore = ColdStore::create(['name' => 'براد 3', 'code' => 'BR-3', 'crate_standard_id' => $standard->id]);
    $custodian = Custodian::create(['name' => 'Mahmoud']);

    $load = PomegranateLoad::create([
        'load_number' => 'LOAD-CUSTODY-003',
        'supplier_id' => $supplier->id,
        'vehicle_id' => $vehicle->id,
        'received_at' => now(),
        'loaded_crates_count' => 21,
        'loaded_weight_kg' => 472.5,
        'on_vehicle_crates_count' => 21,
        'on_vehicle_weight_kg' => 472.5,
        'available_crates_count' => 0,
        'available_weight_kg' => 0,
        'status' => 'open',
    ]);

    app(MoveLoadToColdStoreAction::class)->execute($load, $coldStore, 21, 472.5);

    $transaction = app(IssueCratesToCustodianAction::class)->execute($coldStore, $load, $custodian, 21);

    expect((float) $transaction->weight_kg)->toBe(472.5)
        ->and($coldStore->fresh()->current_crates_count)->toBe(0)
        ->and((float) $coldStore->fresh()->current_weight_kg)->toBe(0.0);
});

test('a custodian cannot return more than the open custody', function () {
    $supplier = Supplier::create(['name' => 'Supplier One']);
    $vehicle = Vehicle::create(['plate_number' => 'BBB-222', 'type' => 'Truck']);
    $coldStore = ColdStore::create(['name' => 'براد 2', 'code' => 'BR-2']);
    $custodian = Custodian::create(['name' => 'Ahmed']);

    $load = PomegranateLoad::create([
        'load_number' => 'LOAD-CUSTODY-002',
        'supplier_id' => $supplier->id,
        'vehicle_id' => $vehicle->id,
        'received_at' => now(),
        'loaded_crates_count' => 50,
        'loaded_weight_kg' => 1000,
        'on_vehicle_crates_count' => 50,
        'on_vehicle_weight_kg' => 1000,
        'available_crates_count' => 0,
        'available_weight_kg' => 0,
        'status' => 'open',
    ]);

    app(MoveLoadToColdStoreAction::class)->execute($load, $coldStore, 20, 400);
    app(IssueCratesToCustodianAction::class)->execute($coldStore, $load, $custodian, 7, 140);

    expect(fn () => app(ReturnCustodyAction::class)->execute($coldStore, $load, $custodian, 8, 160, 'vehicle'))
        ->toThrow(\RuntimeException::class);
});

test('a cold store cannot close while stock or custody is outstanding and can close after everything is settled', function () {
    $supplier = Supplier::create(['name' => 'Supplier Three']);
    $vehicle = Vehicle::create(['plate_number' => 'DDD-444', 'type' => 'Truck']);
    $coldStore = ColdStore::create(['name' => 'براد 4', 'code' => 'BR-4']);
    $custodian = Custodian::create(['name' => 'Mahmoud']);

    $load = PomegranateLoad::create([
        'load_number' => 'LOAD-CUSTODY-004',
        'supplier_id' => $supplier->id,
        'vehicle_id' => $vehicle->id,
        'received_at' => now(),
        'loaded_crates_count' => 10,
        'loaded_weight_kg' => 200,
        'on_vehicle_crates_count' => 10,
        'on_vehicle_weight_kg' => 200,
        'available_crates_count' => 0,
        'available_weight_kg' => 0,
        'status' => 'open',
    ]);

    app(MoveLoadToColdStoreAction::class)->execute($load, $coldStore, 10, 200);

    expect(fn () => app(CloseColdStoreAction::class)->execute($coldStore))
        ->toThrow(\RuntimeException::class);

    app(IssueCratesToCustodianAction::class)->execute($coldStore, $load, $custodian, 10, 200);

    expect(fn () => app(CloseColdStoreAction::class)->execute($coldStore))
        ->toThrow(\RuntimeException::class);

    app(ReturnCustodyAction::class)->execute($coldStore, $load, $custodian, 10, 200, 'vehicle');
    $closed = app(CloseColdStoreAction::class)->execute($coldStore->fresh());

    expect($closed->is_active)->toBeFalse();
});
