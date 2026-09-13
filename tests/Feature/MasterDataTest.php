<?php

use App\Models\ColdStore;
use App\Models\CrateStandard;
use App\Models\Custodian;
use App\Models\FinishedProduct;
use App\Models\Vehicle;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function masterDataUser(string $role): User
{
    return User::factory()->create(['role' => $role, 'is_active' => true]);
}

test('only admin and manager can access master data', function () {
    $admin = masterDataUser('admin');
    $manager = masterDataUser('manager');
    $storekeeper = masterDataUser('storekeeper');
    $accountant = masterDataUser('accountant');

    $this->actingAs($admin)->get('/master-data')->assertOk()->assertSee('البيانات الأساسية');
    $this->actingAs($manager)->get('/master-data')->assertOk();
    $this->actingAs($storekeeper)->get('/master-data')->assertForbidden();
    $this->actingAs($accountant)->get('/master-data')->assertForbidden();
});

test('manager can create master data records', function () {
    $manager = masterDataUser('manager');

    $standardResponse = $this->actingAs($manager)->post('/master-data/crate-standards', [
        'name' => '25kg Standard',
        'gross_weight_kg' => 25,
        'tare_weight_kg' => 2.5,
    ]);
    $standardResponse->assertRedirect('/master-data');
    $standard = CrateStandard::firstOrFail();

    $this->actingAs($manager)->post('/master-data/vehicles', [
        'plate_number' => 'MD-001',
        'type' => 'Trailer',
        'driver_name' => 'Ali',
    ])->assertRedirect('/master-data');

    $this->actingAs($manager)->post('/master-data/cold-stores', [
        'name' => 'براد 1',
        'code' => 'BR-1',
        'crate_standard_id' => $standard->id,
    ])->assertRedirect('/master-data');

    $this->actingAs($manager)->post('/master-data/custodians', [
        'name' => 'Mahmoud',
        'phone' => '01000000000',
    ])->assertRedirect('/master-data');

    $this->actingAs($manager)->post('/master-data/products', [
        'name' => 'Pomegranate Arils',
        'code' => 'arils',
        'type' => 'peeling',
        'unit' => 'kg',
    ])->assertRedirect('/master-data');

    expect(Vehicle::where('plate_number', 'MD-001')->exists())->toBeTrue()
        ->and(ColdStore::where('code', 'BR-1')->exists())->toBeTrue()
        ->and(Custodian::where('name', 'Mahmoud')->exists())->toBeTrue()
        ->and(FinishedProduct::where('code', 'arils')->exists())->toBeTrue();
});

test('master data can be deactivated but cold store uses safe close rules', function () {
    $manager = masterDataUser('manager');
    $custodian = Custodian::create(['name' => 'Ahmed', 'is_active' => true]);
    $standard = CrateStandard::create(['name' => 'Standard', 'gross_weight_kg' => 25, 'tare_weight_kg' => 2.5, 'is_active' => true]);
    $product = FinishedProduct::create(['name' => 'Juice', 'code' => 'juice-test', 'type' => 'juice', 'unit' => 'kg', 'is_active' => true]);
    $coldStore = ColdStore::create(['name' => 'براد 2', 'code' => 'BR-2', 'crate_standard_id' => $standard->id, 'is_active' => true]);

    $this->actingAs($manager)->post("/master-data/custodians/{$custodian->id}/toggle")
        ->assertRedirect('/master-data');
    $this->actingAs($manager)->post("/master-data/crate-standards/{$standard->id}/toggle")
        ->assertRedirect('/master-data');
    $this->actingAs($manager)->post("/master-data/products/{$product->id}/toggle")
        ->assertRedirect('/master-data');

    expect($custodian->fresh()->is_active)->toBeFalse()
        ->and($standard->fresh()->is_active)->toBeFalse()
        ->and($product->fresh()->is_active)->toBeFalse();

    $this->actingAs($manager)->post("/master-data/cold-stores/{$coldStore->id}/close")
        ->assertRedirect('/master-data');

    expect($coldStore->fresh()->is_active)->toBeFalse();
});
