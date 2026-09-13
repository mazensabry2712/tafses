<?php

use App\Actions\CreateFinishedProductSaleAction;
use App\Actions\GetCustomerBalanceAction;
use App\Actions\RecordCustomerPaymentAction;
use App\Models\Customer;
use App\Models\FinishedProduct;
use App\Models\FinishedProductStock;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeFinishedProductStock(float $quantity = 500): FinishedProduct
{
    $product = FinishedProduct::create([
        'name' => 'Pomegranate Arils',
        'code' => 'peeling',
        'type' => 'peeling',
        'unit' => 'kg',
        'is_active' => true,
    ]);

    FinishedProductStock::create([
        'finished_product_id' => $product->id,
        'quantity' => $quantity,
    ]);

    return $product;
}

test('a finished product sale reduces stock and creates customer debt', function () {
    $customer = Customer::create(['name' => 'Customer One']);
    $product = makeFinishedProductStock(500);

    $sale = app(CreateFinishedProductSaleAction::class)->execute(
        $customer,
        [[
            'finished_product_id' => $product->id,
            'quantity' => 100,
            'unit_price' => 80,
        ]],
        'INV-0001',
        3000,
    );

    expect((float) $sale->total_amount)->toBe(8000.0)
        ->and((float) $sale->paid_amount)->toBe(3000.0)
        ->and($sale->status)->toBe('partial')
        ->and((float) FinishedProductStock::where('finished_product_id', $product->id)->value('quantity'))->toBe(400.0)
        ->and(app(GetCustomerBalanceAction::class)->execute($customer)['balance_due'])->toBe(5000.0)
        ->and($product->transactions()->where('type', 'sale')->count())->toBe(1)
        ->and((float) $product->transactions()->where('type', 'sale')->first()->quantity)->toBe(-100.0);
});

test('a customer payment settles an invoice and updates the customer balance', function () {
    $customer = Customer::create(['name' => 'Customer Two']);
    $product = makeFinishedProductStock(100);

    $sale = app(CreateFinishedProductSaleAction::class)->execute(
        $customer,
        [[
            'finished_product_id' => $product->id,
            'quantity' => 50,
            'unit_price' => 100,
        ]],
        'INV-0002',
    );

    app(RecordCustomerPaymentAction::class)->execute($customer, 2500, $sale, 'cash');
    $sale->refresh();

    expect($sale->status)->toBe('partial')
        ->and((float) $sale->paid_amount)->toBe(2500.0)
        ->and(app(GetCustomerBalanceAction::class)->execute($customer)['balance_due'])->toBe(2500.0);

    app(RecordCustomerPaymentAction::class)->execute($customer, 2500, $sale, 'cash');
    $sale->refresh();

    expect($sale->status)->toBe('paid')
        ->and((float) $sale->paid_amount)->toBe(5000.0)
        ->and(app(GetCustomerBalanceAction::class)->execute($customer)['balance_due'])->toBe(0.0);
});

test('a sale cannot exceed finished product stock or invoice payment', function () {
    $customer = Customer::create(['name' => 'Customer Three']);
    $product = makeFinishedProductStock(10);
    $action = app(CreateFinishedProductSaleAction::class);

    expect(fn () => $action->execute($customer, [[
        'finished_product_id' => $product->id,
        'quantity' => 11,
        'unit_price' => 50,
    ]], 'INV-0003'))->toThrow(RuntimeException::class);

    expect(fn () => $action->execute($customer, [[
        'finished_product_id' => $product->id,
        'quantity' => 10,
        'unit_price' => 50,
    ]], 'INV-0004', 501))->toThrow(InvalidArgumentException::class);
});
