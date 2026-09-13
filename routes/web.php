<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
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

    Route::middleware('role:admin,manager')->group(function () {
        Route::get('/management', fn () => 'Management area')->name('management');
    });

    Route::middleware('role:admin,manager,storekeeper')->group(function () {
        Route::get('/warehouse', fn () => 'Warehouse area')->name('warehouse');
    });

    Route::middleware('role:admin,manager,accountant')->group(function () {
        Route::get('/accounting', fn () => 'Accounting area')->name('accounting');
    });
});
