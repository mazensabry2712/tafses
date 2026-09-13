<?php

use App\Actions\MoveLoadToColdStoreAction;
use App\Models\ColdStore;
use App\Models\Customer;
use App\Models\FinishedProduct;
use App\Models\FinishedProductStock;
use App\Models\PomegranateLoad;
use App\Models\PomegranatePurchase;
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
    $this->actingAs($manager)->get('/warehouse')->assertOk()->assertSee('المخازن والثلاجات');
});

test('custody management page is permission protected and renders for storekeeper', function () {
    $worker = httpUser('worker');
    $storekeeper = httpUser('storekeeper');

    $this->actingAs($worker)->get('/warehouse/custody')->assertForbidden();

    $this->actingAs($storekeeper)
        ->get('/warehouse/custody')
        ->assertOk()
        ->assertViewIs('warehouse.custody')
        ->assertSee('إدارة العُهد');
});

test('reports are available to operational roles but not worker', function () {
    $worker = httpUser('worker');
    $storekeeper = httpUser('storekeeper');

    $this->actingAs($worker)->get('/reports')->assertForbidden();
    $this->actingAs($storekeeper)->get('/reports')->assertOk()->assertViewIs('reports.index');
});

test('reports accept a custom date range', function () {
    $manager = httpUser('manager');

    $this->actingAs($manager)
        ->get('/reports?from=2026-09-01&to=2026-09-07')
        ->assertOk()
        ->assertViewIs('reports.index')
        ->assertSee('2026-09-01')
        ->assertSee('2026-09-07');
});

test('receiving page is permission protected and renders for authorized users', function () {
    $worker = httpUser('worker');
    $storekeeper = httpUser('storekeeper');

    $this->actingAs($worker)->get('/receiving')->assertForbidden();
    $this->actingAs($storekeeper)->get('/receiving')->assertOk()->assertViewIs('receiving.index')->assertSee('استلام شحنة رمان');
});

test('processing page is permission protected and renders for authorized users', function () {
    $accountant = httpUser('accountant');
    $storekeeper = httpUser('storekeeper');

    $this->actingAs($accountant)->get('/processing')->assertForbidden();
    $this->actingAs($storekeeper)->get('/processing')->assertOk()->assertViewIs('processing.index')->assertSee('تسجيل دفعة تصنيع');
});

test('sales page is permission protected and renders for sales users', function () {
    $worker = httpUser('worker');
    $accountant = httpUser('accountant');

    $this->actingAs($worker)->get('/sales')->assertForbidden();
    $this->actingAs($accountant)->get('/sales')->assertOk()->assertViewIs('sales.index')->assertSee('المبيعات والعملاء');
});

test('customer account page is permission protected and renders for customer managers', function () {
    $customer = Customer::create(['name' => 'HTTP Customer']);
    $worker = httpUser('worker');
    $accountant = httpUser('accountant');

    $this->actingAs($worker)->get("/sales/customers/{$customer->id}")->assertForbidden();
    $this->actingAs($accountant)->get("/sales/customers/{$customer->id}")->assertOk()->assertViewIs('sales.customer')->assertSee('HTTP Customer');
});

test('supplier pages are permission protected and render for accountant', function () {
    $supplier = Supplier::create(['name' => 'HTTP Supplier']);
    $worker = httpUser('worker');
    $accountant = httpUser('accountant');

    $this->actingAs($worker)->get('/suppliers')->assertForbidden();
    $this->actingAs($accountant)->get('/suppliers')->assertOk()->assertViewIs('suppliers.index')->assertSee('HTTP Supplier');
    $this->actingAs($accountant)->get("/suppliers/{$supplier->id}")->assertOk()->assertViewIs('suppliers.show')->assertSee('HTTP Supplier');
});

test('audit page is permission protected and renders for admin', function () {
    $storekeeper = httpUser('storekeeper');
    $admin = httpUser('admin');

    $this->actingAs($storekeeper)->get('/audit')->assertForbidden();
    $this->actingAs($admin)->get('/audit')->assertOk()->assertViewIs('audit.index')->assertSee('سجل التدقيق');
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
    $coldStore = ColdStore::create(['name' => 'HTTP Store', 'code' => 'HTTP-STORE']);
    app(MoveLoadToColdStoreAction::class)->execute($load, $coldStore, 100, 2000);

    $response = $this->from('/dashboard')->actingAs($manager)->post("/processing/loads/{$load->id}", [
        'cold_store_id' => $coldStore->id,
        'process_type' => 'peeling',
        'input_crates_count' => 20,
        'input_weight_kg' => 400,
        'output_weight_kg' => 250,
        'waste_weight_kg' => 50,
    ]);

    $response->assertRedirect('/dashboard');
    expect(FinishedProduct::where('code', 'peeling')->exists())->toBeTrue()
        ->and($coldStore->fresh()->current_crates_count)->toBe(80)
        ->and((float) $coldStore->fresh()->current_weight_kg)->toBe(1600.0);
});

test('supplier purchase and payment HTTP routes update the supplier balance', function () {
    $accountant = httpUser('accountant');
    $supplier = Supplier::create(['name' => 'Purchase Supplier']);
    $vehicle = Vehicle::create(['plate_number' => 'SUP-001', 'type' => 'Trailer']);
    $load = PomegranateLoad::create([
        'load_number' => 'PUR-LOAD-001',
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

    $response = $this->from('/suppliers')->actingAs($accountant)->post("/suppliers/{$supplier->id}/purchases", [
        'pomegranate_load_id' => $load->id,
        'pricing_unit' => 'kg',
        'unit_price' => 10,
        'initial_paid' => 5000,
    ]);

    $response->assertRedirect('/suppliers');
    $purchase = PomegranatePurchase::where('pomegranate_load_id', $load->id)->firstOrFail();
    expect((float) $purchase->total_amount)->toBe(20000.0)
        ->and((float) $purchase->paid_amount)->toBe(5000.0);

    $response = $this->from('/suppliers/'. $supplier->id)->actingAs($accountant)->post("/suppliers/{$supplier->id}/purchases/{$purchase->id}/payments", [
        'amount' => 15000,
        'payment_method' => 'bank',
    ]);

    $response->assertRedirect('/suppliers/'. $supplier->id);
    expect((float) PomegranatePurchase::findOrFail($purchase->id)->paid_amount)->toBe(20000.0);
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

    $response = $this->from('/sales')->actingAs($accountant)->post("/sales/customers/{$customer->id}/invoices", [
        'invoice_number' => 'HTTP-INV-001',
        'items' => [[
            'finished_product_id' => $product->id,
            'quantity' => 100,
            'unit_price' => 80,
        ]],
        'paid_amount' => 1000,
    ]);

    $response->assertRedirect('/sales');
    expect((float) FinishedProductStock::where('finished_product_id', $product->id)->value('quantity'))->toBe(400.0);
});
