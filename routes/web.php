<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Support\Permissions;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'loginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/management', fn () => 'Management area')
        ->middleware('permission:'.Permissions::MANAGE_USERS)
        ->name('management');

    Route::get('/warehouse', fn () => 'Warehouse area')
        ->middleware('permission:'.Permissions::MANAGE_COLD_STORES)
        ->name('warehouse');

    Route::get('/accounting', fn () => 'Accounting area')
        ->middleware('permission:'.Permissions::MANAGE_PAYMENTS)
        ->name('accounting');
});
