<?php

use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProcessingController;
use App\Http\Controllers\ReceivingController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\WarehouseController;
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

    Route::post('/receiving/loads', [ReceivingController::class, 'store'])
        ->middleware('permission:'.Permissions::RECEIVE_LOADS)
        ->name('receiving.loads.store');

    Route::post('/warehouse/loads/{load}/cold-store', [WarehouseController::class, 'moveToColdStore'])
        ->middleware('permission:'.Permissions::MANAGE_COLD_STORES)
        ->name('warehouse.loads.cold-store');

    Route::post('/warehouse/{coldStore}/loads/{load}/custodians/{custodian}/issue', [WarehouseController::class, 'issue'])
        ->middleware('permission:'.Permissions::MANAGE_CUSTODY)
        ->name('warehouse.custody.issue');

    Route::post('/warehouse/{coldStore}/loads/{load}/custodians/{custodian}/return', [WarehouseController::class, 'returnCustody'])
        ->middleware('permission:'.Permissions::MANAGE_CUSTODY)
        ->name('warehouse.custody.return');

    Route::post('/warehouse/{coldStore}/close', [WarehouseController::class, 'close'])
        ->middleware('permission:'.Permissions::MANAGE_COLD_STORES)
        ->name('warehouse.close');

    Route::post('/processing/loads/{load}', [ProcessingController::class, 'store'])
        ->middleware('permission:'.Permissions::PROCESS_POMEGRANATES)
        ->name('processing.store');

    Route::get('/reports', [ReportsController::class, 'index'])
        ->middleware('permission:'.Permissions::VIEW_REPORTS)
        ->name('reports.index');

    Route::get('/accounting', fn () => 'Accounting area')
        ->middleware('permission:'.Permissions::MANAGE_PAYMENTS)
        ->name('accounting');

    Route::post('/sales/customers/{customer}/invoices', [SalesController::class, 'store'])
        ->middleware('permission:'.Permissions::MANAGE_SALES)
        ->name('sales.store');

    Route::post('/sales/customers/{customer}/invoices/{sale}/payments', [SalesController::class, 'payment'])
        ->middleware('permission:'.Permissions::MANAGE_PAYMENTS)
        ->name('sales.payment');

    Route::get('/audit', [AuditLogController::class, 'index'])
        ->middleware('permission:'.Permissions::VIEW_AUDIT)
        ->name('audit.index');
});
