<?php

use App\Models\Customer;
use App\Models\PomegranateLoad;
use App\Models\PomegranatePurchase;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function auditUser(string $role): User
{
    return User::factory()->create([
        'role' => $role,
        'is_active' => true,
    ]);
}

test('management and accounting placeholders are replaced by real screens', function () {
    $worker = auditUser('worker');
    $admin = auditUser('admin');
    $accountant = auditUser('accountant');

    $this->actingAs($worker)->get('/management')->assertForbidden();
    $this->actingAs($admin)->get('/management')->assertOk()->assertViewIs('management.index')->assertSee('إدارة المستخدمين');
    $this->actingAs($worker)->get('/accounting')->assertForbidden();
    $this->actingAs($accountant)->get('/accounting')->assertOk()->assertViewIs('accounting.index')->assertSee('الحسابات');
});

test('admin can create and deactivate another user but not self', function () {
    $admin = auditUser('admin');

    $this->from('/management')->actingAs($admin)->post('/management/users', [
        'name' => 'Managed Worker',
        'email' => 'managed-worker@tafses.local',
        'role' => 'worker',
        'password' => 'secret123',
    ])->assertRedirect('/management');

    $user = User::where('email', 'managed-worker@tafses.local')->firstOrFail();
    expect($user->is_active)->toBeTrue();

    $this->from('/management')->actingAs($admin)->post("/management/users/{$user->id}/toggle")
        ->assertRedirect('/management');

    expect(User::findOrFail($user->id)->is_active)->toBeFalse();

    $this->from('/management')->actingAs($admin)->post("/management/users/{$admin->id}/toggle")
        ->assertRedirect('/management')
        ->assertSessionHasErrors('user');

    expect(User::findOrFail($admin->id)->is_active)->toBeTrue();
});

test('a load can have only one purchase record', function () {
    $accountant = auditUser('accountant');
    $supplier = Supplier::create(['name' => 'Unique Supplier']);
    $vehicle = Vehicle::create(['plate_number' => 'UNQ-AUDIT', 'type' => 'Trailer']);
    $load = PomegranateLoad::create([
        'load_number' => 'UNQ-AUDIT-LOAD',
        'supplier_id' => $supplier->id,
        'vehicle_id' => $vehicle->id,
        'received_at' => now(),
        'loaded_crates_count' => 20,
        'loaded_weight_kg' => 450,
        'on_vehicle_crates_count' => 20,
        'on_vehicle_weight_kg' => 450,
        'available_crates_count' => 0,
        'available_weight_kg' => 0,
        'status' => 'open',
    ]);

    $payload = [
        'pomegranate_load_id' => $load->id,
        'pricing_unit' => 'kg',
        'unit_price' => 10,
    ];

    $this->from('/suppliers')->actingAs($accountant)
        ->post("/suppliers/{$supplier->id}/purchases", $payload)
        ->assertRedirect('/suppliers');

    $this->from('/suppliers')->actingAs($accountant)
        ->post("/suppliers/{$supplier->id}/purchases", $payload)
        ->assertRedirect('/suppliers')
        ->assertSessionHasErrors('pomegranate_load_id');

    expect(PomegranatePurchase::where('pomegranate_load_id', $load->id)->count())->toBe(1);
});

test('accounting shows customer and supplier balances', function () {
    $accountant = auditUser('accountant');
    $customer = Customer::create(['name' => 'Accounting Customer']);
    $supplier = Supplier::create(['name' => 'Accounting Supplier']);

    $this->actingAs($accountant)->get('/accounting')
        ->assertOk()
        ->assertSee('Accounting Customer')
        ->assertSee('Accounting Supplier');
});
