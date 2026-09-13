<?php

use App\Actions\CreateFinishedProductStockAdjustmentAction;
use App\Models\FinishedProduct;
use App\Models\FinishedProductStock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeAdjustableFinishedProduct(float $quantity = 100): FinishedProduct
{
    $product = FinishedProduct::create([
        'name' => 'Pomegranate Juice',
        'code' => 'juice',
        'type' => 'juice',
        'unit' => 'kg',
        'is_active' => true,
    ]);

    FinishedProductStock::create([
        'finished_product_id' => $product->id,
        'quantity' => $quantity,
    ]);

    return $product;
}

test('a finished product stock increase records an immutable adjustment and ledger movement', function () {
    $product = makeAdjustableFinishedProduct(100);
    $user = User::factory()->create();

    $adjustment = app(CreateFinishedProductStockAdjustmentAction::class)->execute(
        $product,
        'increase',
        25.5,
        'Physical count found extra finished stock.',
        $user->id,
    );

    expect($adjustment->direction)->toBe('increase')
        ->and((float) $adjustment->quantity)->toBe(25.5)
        ->and($adjustment->reason)->toBe('Physical count found extra finished stock.')
        ->and($adjustment->recorded_by)->toBe($user->id)
        ->and((float) $product->fresh()->stock->quantity)->toBe(125.5)
        ->and((float) $product->transactions()->where('type', 'adjustment_in')->first()->quantity)->toBe(25.5)
        ->and($product->stockAdjustments()->count())->toBe(1);
});

test('a finished product stock decrease cannot make stock negative', function () {
    $product = makeAdjustableFinishedProduct(10);
    $action = app(CreateFinishedProductStockAdjustmentAction::class);

    $action->execute($product, 'decrease', 4, 'Damaged product discarded.');

    expect((float) $product->fresh()->stock->quantity)->toBe(6.0)
        ->and((float) $product->transactions()->where('type', 'adjustment_out')->first()->quantity)->toBe(-4.0);

    expect(fn () => $action->execute($product, 'decrease', 7, 'Second damaged batch.'))
        ->toThrow(\RuntimeException::class);

    expect((float) $product->fresh()->stock->quantity)->toBe(6.0)
        ->and($product->stockAdjustments()->count())->toBe(1);
});

test('stock adjustment validates direction quantity and reason', function () {
    $product = makeAdjustableFinishedProduct();
    $action = app(CreateFinishedProductStockAdjustmentAction::class);

    expect(fn () => $action->execute($product, 'wrong', 1, 'Reason'))->toThrow(\InvalidArgumentException::class);
    expect(fn () => $action->execute($product, 'increase', 0, 'Reason'))->toThrow(\InvalidArgumentException::class);
    expect(fn () => $action->execute($product, 'increase', 1, '   '))->toThrow(\InvalidArgumentException::class);
});
