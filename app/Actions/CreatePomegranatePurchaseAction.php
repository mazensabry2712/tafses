<?php

namespace App\Actions;

use App\Models\PomegranateLoad;
use App\Models\PomegranatePurchase;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CreatePomegranatePurchaseAction
{
    public function execute(
        PomegranateLoad $load,
        string $pricingUnit,
        float $unitPrice,
        ?float $quantity = null,
        ?float $initialPaid = null,
        ?string $notes = null,
    ): PomegranatePurchase {
        if ($load->supplier_id === null) {
            throw new InvalidArgumentException('A supplier is required before creating a purchase.');
        }

        if (!in_array($pricingUnit, ['kg', 'crate', 'fixed'], true)) {
            throw new InvalidArgumentException('Pricing unit must be kg, crate, or fixed.');
        }

        if ($unitPrice <= 0) {
            throw new InvalidArgumentException('Unit price must be positive.');
        }

        $quantity = $quantity ?? match ($pricingUnit) {
            'kg' => (float) $load->loaded_weight_kg,
            'crate' => (float) $load->loaded_crates_count,
            'fixed' => 1.0,
        };

        if ($quantity <= 0) {
            throw new InvalidArgumentException('Purchase quantity must be positive.');
        }

        $totalAmount = round($unitPrice * $quantity, 3);
        $initialPaid = $initialPaid ?? 0.0;

        if ($initialPaid < 0 || $initialPaid > $totalAmount) {
            throw new InvalidArgumentException('Initial paid amount cannot exceed the purchase total.');
        }

        return DB::transaction(function () use ($load, $pricingUnit, $unitPrice, $quantity, $totalAmount, $initialPaid, $notes) {
            $purchase = PomegranatePurchase::create([
                'pomegranate_load_id' => $load->id,
                'supplier_id' => $load->supplier_id,
                'pricing_unit' => $pricingUnit,
                'unit_price' => $unitPrice,
                'quantity' => $quantity,
                'total_amount' => $totalAmount,
                'paid_amount' => $initialPaid,
                'purchased_at' => $load->received_at->toDateString(),
                'notes' => $notes,
            ]);

            if ($initialPaid > 0) {
                $purchase->payments()->create([
                    'supplier_id' => $purchase->supplier_id,
                    'amount' => $initialPaid,
                    'paid_at' => now(),
                    'payment_method' => 'cash',
                    'notes' => 'Initial payment recorded with purchase.',
                ]);
            }

            return $purchase;
        });
    }
}
