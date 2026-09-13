<?php

use App\Http\Controllers\AccountingController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ManagementController;
use App\Http\Controllers\MasterDataController;
use App\Http\Controllers\ProcessingController;
use App\Http\Controllers\ReceivingController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\SuppliersController;
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

    Route::get('/management', [ManagementController::class, 'index'])
        ->middleware('permission:'.Permissions::MANAGE_USERS)
        ->name('management');
    Route::post('/management/users', [ManagementController::class, 'store'])
        ->middleware('permission:'.Permissions::MANAGE_USERS)
        ->name('management.store');
    Route::post('/management/users/{user}/toggle', [ManagementController::class, 'toggleActive'])
        ->middleware('permission:'.Permissions::MANAGE_USERS)
        ->name('management.toggle');

    Route::get('/master-data', [MasterDataController::class, 'index'])
        ->middleware('permission:'.Permissions::MANAGE_MASTER_DATA)
        ->name('master-data');
    Route::post('/master-data/vehicles', [MasterDataController::class, 'storeVehicle'])
        ->middleware('permission:'.Permissions::MANAGE_MASTER_DATA)
        ->name('master-data.vehicles.store');
    Route::post('/master-data/cold-stores', [MasterDataController::class, 'storeColdStore'])
        ->middleware('permission:'.Permissions::MANAGE_MASTER_DATA)
        ->name('master-data.cold-stores.store');
    Route::post('/master-data/cold-stores/{coldStore}/close', [MasterDataController::class, 'closeColdStore'])
        ->middleware('permission:'.Permissions::MANAGE_MASTER_DATA)
        ->name('master-data.cold-stores.close');
    Route::post('/master-data/custodians', [MasterDataController::class, 'storeCustodian'])
        ->middleware('permission:'.Permissions::MANAGE_MASTER_DATA)
        ->name('master-data.custodians.store');
    Route::post('/master-data/custodians/{custodian}/toggle', [MasterDataController::class, 'toggleCustodian'])
        ->middleware('permission:'.Permissions::MANAGE_MASTER_DATA)
        ->name('master-data.custodians.toggle');
    Route::post('/master-data/crate-standards', [MasterDataController::class, 'storeCrateStandard'])
        ->middleware('permission:'.Permissions::MANAGE_MASTER_DATA)
        ->name('master-data.crate-standards.store');
    Route::post('/master-data/crate-standards/{crateStandard}/toggle', [MasterDataController::class, 'toggleCrateStandard'])
        ->middleware('permission:'.Permissions::MANAGE_MASTER_DATA)
        ->name('master-data.crate-standards.toggle');
    Route::post('/master-data/products', [MasterDataController::class, 'storeProduct'])
        ->middleware('permission:'.Permissions::MANAGE_MASTER_DATA)
        ->name('master-data.products.store');
    Route::post('/master-data/products/{product}/toggle', [MasterDataController::class, 'toggleProduct'])
        ->middleware('permission:'.Permissions::MANAGE_MASTER_DATA)
        ->name('master-data.products.toggle');

    Route::get('/warehouse', [WarehouseController::class, 'index'])
        ->middleware('permission:'.Permissions::MANAGE_COLD_STORES)
        ->name('warehouse');
    Route::get('/warehouse/custody', [WarehouseController::class, 'custody'])
        ->middleware('permission:'.Permissions::MANAGE_CUSTODY)
        ->name('warehouse.custody');

    Route::get('/receiving', [ReceivingController::class, 'index'])
        ->middleware('permission:'.Permissions::RECEIVE_LOADS)
        ->name('receiving.index');
    Route::post('/receiving/loads', [ReceivingController::class, 'store'])
        ->middleware('permission:'.Permissions::RECEIVE_LOADS)
        ->name('receiving.loads.store');

    Route::get('/processing', [ProcessingController::class, 'index'])
        ->middleware('permission:'.Permissions::PROCESS_POMEGRANATES)
        ->name('processing.index');
    Route::post('/processing/loads/{load}', [ProcessingController::class, 'store'])
        ->middleware('permission:'.Permissions::PROCESS_POMEGRANATES)
        ->name('processing.store');

    Route::get('/sales', [SalesController::class, 'index'])
        ->middleware('permission:'.Permissions::MANAGE_SALES)
        ->name('sales.index');
    Route::get('/sales/customers/{customer}', [SalesController::class, 'customer'])
        ->middleware('permission:'.Permissions::MANAGE_CUSTOMERS)
        ->name('sales.customer');
    Route::post('/sales/customers/{customer}/invoices', [SalesController::class, 'store'])
        ->middleware('permission:'.Permissions::MANAGE_SALES)
        ->name('sales.store');
    Route::post('/sales/customers/{customer}/invoices/{sale}/payments', [SalesController::class, 'payment'])
        ->middleware('permission:'.Permissions::MANAGE_PAYMENTS)
        ->name('sales.payment');

    Route::get('/suppliers', [SuppliersController::class, 'index'])
        ->middleware('permission:'.Permissions::MANAGE_SUPPLIERS)
        ->name('suppliers.index');
    Route::post('/suppliers', [SuppliersController::class, 'store'])
        ->middleware('permission:'.Permissions::MANAGE_SUPPLIERS)
        ->name('suppliers.store');
    Route::get('/suppliers/{supplier}', [SuppliersController::class, 'show'])
        ->middleware('permission:'.Permissions::MANAGE_SUPPLIERS)
        ->name('suppliers.show');
    Route::post('/suppliers/{supplier}/purchases', [SuppliersController::class, 'purchaseStore'])
        ->middleware('permission:'.Permissions::MANAGE_SUPPLIERS)
        ->name('suppliers.purchases.store');
    Route::post('/suppliers/{supplier}/purchases/{purchase}/payments', [SuppliersController::class, 'paymentStore'])
        ->middleware('permission:'.Permissions::MANAGE_PAYMENTS)
        ->name('suppliers.purchases.payments.store');

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

    Route::get('/reports', [ReportsController::class, 'index'])
        ->middleware('permission:'.Permissions::VIEW_REPORTS)
        ->name('reports.index');
    Route::get('/accounting', [AccountingController::class, 'index'])
        ->middleware('permission:'.Permissions::MANAGE_PAYMENTS)
        ->name('accounting');
    Route::get('/audit', [AuditLogController::class, 'index'])
        ->middleware('permission:'.Permissions::VIEW_AUDIT)
        ->name('audit.index');
});
