<?php

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('authenticated requests create audit logs with user and request details', function () {
    $user = User::factory()->create([
        'role' => 'admin',
        'is_active' => true,
    ]);

    $this->actingAs($user)->get('/dashboard')->assertOk();

    $log = AuditLog::query()->latest('id')->first();

    expect($log)->not->toBeNull()
        ->and($log->user_id)->toBe($user->id)
        ->and($log->role)->toBe('admin')
        ->and($log->route)->toBe('dashboard')
        ->and($log->method)->toBe('GET')
        ->and($log->path)->toBe('dashboard')
        ->and($log->status_code)->toBe(200)
        ->and($log->action)->toBe('view');
});

test('only users with audit permission can view audit logs', function () {
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    $storekeeper = User::factory()->create(['role' => 'storekeeper', 'is_active' => true]);

    $this->actingAs($admin)->get('/audit')->assertOk();
    $this->actingAs($storekeeper)->get('/audit')->assertForbidden();
});
