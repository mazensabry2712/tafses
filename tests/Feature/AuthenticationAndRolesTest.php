<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('active users can log in and reach the dashboard', function () {
    $user = User::factory()->create([
        'password' => Hash::make('secret123'),
        'role' => 'manager',
        'is_active' => true,
    ]);

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'secret123',
    ])->assertRedirect('/dashboard');

    $this->assertAuthenticatedAs($user);
    $this->get('/dashboard')->assertOk()->assertSee('manager');
});

test('inactive users cannot log in', function () {
    $user = User::factory()->create([
        'password' => Hash::make('secret123'),
        'is_active' => false,
    ]);

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'secret123',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});

test('permission middleware protects management routes', function () {
    $worker = User::factory()->create(['role' => 'worker']);
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($worker)->get('/management')->assertForbidden();
    $this->actingAs($admin)->get('/management')->assertOk();
});

test('permission middleware allows storekeeper access only to warehouse area', function () {
    $storekeeper = User::factory()->create(['role' => 'storekeeper']);

    $this->actingAs($storekeeper)->get('/warehouse')->assertOk();
    $this->actingAs($storekeeper)->get('/accounting')->assertForbidden();
});

test('logout invalidates the authenticated session', function () {
    $user = User::factory()->create(['role' => 'admin']);

    $this->actingAs($user)->post('/logout')->assertRedirect('/login');
    $this->assertGuest();
});
