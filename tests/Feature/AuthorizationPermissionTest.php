<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function permissionUser(string $role): User
{
    return User::factory()->create([
        'role' => $role,
        'is_active' => true,
    ]);
}

test('admin can access all protected areas', function () {
    $user = permissionUser('admin');

    $this->actingAs($user)->get('/management')->assertOk();
    $this->actingAs($user)->get('/warehouse')->assertOk();
    $this->actingAs($user)->get('/accounting')->assertOk();
});

test('storekeeper can access warehouse but not management or accounting', function () {
    $user = permissionUser('storekeeper');

    $this->actingAs($user)->get('/warehouse')->assertOk();
    $this->actingAs($user)->get('/management')->assertForbidden();
    $this->actingAs($user)->get('/accounting')->assertForbidden();
});

test('accountant can access accounting but not warehouse or management', function () {
    $user = permissionUser('accountant');

    $this->actingAs($user)->get('/accounting')->assertOk();
    $this->actingAs($user)->get('/warehouse')->assertForbidden();
    $this->actingAs($user)->get('/management')->assertForbidden();
});

test('worker can access only permissions explicitly granted to the worker role', function () {
    $user = permissionUser('worker');

    expect($user->canPermission('process_pomegranates'))->toBeTrue()
        ->and($user->canPermission('manage_sales'))->toBeFalse()
        ->and($user->canPermission('manage_cold_stores'))->toBeFalse()
        ->and($user->canPermission('view_reports'))->toBeFalse();
});

test('inactive user cannot use permission even when the role normally has it', function () {
    $user = User::factory()->create([
        'role' => 'admin',
        'is_active' => false,
    ]);

    expect($user->canPermission('manage_users'))->toBeFalse();
    $this->actingAs($user)->get('/management')->assertForbidden();
});
