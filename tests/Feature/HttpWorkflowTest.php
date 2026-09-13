<?php

use App\Models\Customer;
use App\Models\FinishedProduct;
use App\Models\FinishedProductStock;
use App\Models\PomegranateLoad;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function httpUser(string $role): User
{
    return User::factory()->create([
        'role' => $role,
        'is_active' => true,
    ]);
}

test('warehouse HTTP actions are permission protected', function () {
    $worker = httpUser('worker');
    $manager = httpUser('manager');

    $this->actingAs($worker)->get('/warehouse')->assertForbidden();
    $this->actingAs($manager)->get('/warehouse')->assertOk()->assertSee('Warehouse area');
});

test('reports are available to operational roles but not worker', function () {
    $worker = httpUser('worker');
    $storekeeper = httpUser('storekeeper');

    $this->actingAs($worker)->get('/reports')->assertForbidden();
    $this->actingAs($storekeeper)->get('/reports')->assertOk()->assertViewIs('reports.index');
});

test('receiving page is permission protected and renders for authorized users', function () {
    $worker = httpUser('worker');
    $storekeeper = httpUser('storekeeper');

    $this->actingAs($worker)->get('/receiving')->assertForbidden();
    $this->actingAs($storekeeper)->get('/receiving')->assertOk()->assertViewIs('receiving.index')->assertSee('استلام شحنة رمان');
});

test('receiving and processing HTTP routes delegate to domain actions', function () {
    $manager = httpUser('manager');
    $supplier = Supplier::create(['name' => 'HTTP Supplier']);
    $vehicle = Vehicle::create(['plate_number' => 'HTTP-001', 'type' => 'Trailer']);

    $response = $this->from('/dashboard')->actingAs($manager)->post('/receiving/loads', [
        'load_number' => 'HTTP-LOAD-001',
        'supplier_id' => $supplier->id,
        'vehicle_id' => $vehicle->id,
        'crates_count' => 100,
        'weight_kg' => 2000,
    ]);

    $response->assertRedirect('/dashboard');

    $load = PomegranateLoad::where('load_number', 'HTTP-LOAD-001')->firstOrFail();
    $load->update([
        'on_vehicle_crates_count' => 0,
        'on_vehicle_weight_kg' => 0,
        'available_crates_count' => 100,
        'available_weight_kg' => 2000,
        'status' => 'unloaded',
    ]);

    $response = $this->from('/dashboard')->actingAs($manager)->post("/processing/loads/{$load->id}", [
        'process_type' => 'peeling',
        'input_crates_count' => 20,
        'input_weight_kg' => 400,
        'output_weight_kg' => 250,
        'waste_weight_kg' => 50,
    ]);

    $response->assertRedirect('/dashboard');
    expect(FinishedProduct::where('code', 'peeling')->exists())->toBeTrue();
});

test('sales HTTP route reduces finished product stock', function () {
    $accountant = httpUser('accountant');
    $customer = Customer::create(['name' => 'HTTP Customer']);
    $product = FinishedProduct::create([
        'name' => 'Pomegranate Arils',
        'code' => 'peeling',
        'type' => 'peeling',
        'unit' => 'kg',
        'is_active' => true,
    ]);
    FinishedProductStock::create(['finished_product_id' => $product->id, 'quantity' => 500]);

    $response = $this->from('/dashboard')->actingAs($accountant)->post("/sales/customers/{$customer->id}/invoices", [
        'invoice_number' => 'HTTP-INV-001',
        'items' => [[
            'finished_product_id' => $product->id,
            'quantity' => 100,
            'unit_price' => 80,
        ]],
        'paid_amount' => 1000,
    ]);

    $response->assertRedirect('/dashboard');
    expect((float) FinishedProductStock::where('finished_product_id', $product->id)->value('quantity'))->toBe(400.0);
});
