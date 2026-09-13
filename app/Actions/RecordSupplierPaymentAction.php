<?php

namespace App\Actions;

use App\Models\PomegranatePurchase;
use App\Models\SupplierPayment;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class RecordSupplierPaymentAction
{
    public function execute(
        PomegranatePurchase $purchase,
        float $amount,
        string $paymentMethod = 'cash',
        ?string $notes = null,
    ): SupplierPayment {
        if ($amount <= 0) {
            throw new InvalidArgumentException('Payment amount must be positive.');
        }

        if (!in_array($paymentMethod, ['cash', 'bank', 'transfer', 'other'], true)) {
            throw new InvalidArgumentException('Invalid payment method.');
        }

        return DB::transaction(function () use ($purchase, $amount, $paymentMethod, $notes) {
            $purchase = PomegranatePurchase::query()->lockForUpdate()->findOrFail($purchase->id);
            $remaining = round((float) $purchase->total_amount - (float) $purchase->paid_amount, 3);

            if ($amount > $remaining) {
                throw new InvalidArgumentException('Payment cannot exceed the remaining supplier balance.');
            }

            $purchase->paid_amount = round((float) $purchase->paid_amount + $amount, 3);
            $purchase->save();

            return $purchase->payments()->create([
                'supplier_id' => $purchase->supplier_id,
                'amount' => $amount,
                'paid_at' => now(),
                'payment_method' => $paymentMethod,
                'notes' => $notes,
            ]);
        });
    }
}
