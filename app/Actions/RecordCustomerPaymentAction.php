<?php

namespace App\Actions;

use App\Models\Customer;
use App\Models\CustomerPayment;
use App\Models\FinishedProductSale;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class RecordCustomerPaymentAction
{
    public function execute(Customer $customer, float $amount, ?FinishedProductSale $sale = null, ?string $paymentMethod = null, ?int $recordedBy = null, ?string $notes = null): CustomerPayment
    {
        if ($amount <= 0) throw new InvalidArgumentException('Payment amount must be positive.');

        return DB::transaction(function () use ($customer, $amount, $sale, $paymentMethod, $recordedBy, $notes) {
            $customer = Customer::query()->lockForUpdate()->findOrFail($customer->id);
            if (!$customer->is_active) throw new RuntimeException('The customer is inactive.');

            $saleModel = null;
            if ($sale) {
                $saleModel = FinishedProductSale::query()->lockForUpdate()->findOrFail($sale->id);
                if ($saleModel->customer_id !== $customer->id) throw new InvalidArgumentException('The payment customer does not match the sale customer.');
                $remaining = round((float) $saleModel->total_amount - (float) $saleModel->paid_amount, 2);
                if ($amount > $remaining) throw new InvalidArgumentException('Payment amount cannot exceed the remaining invoice balance.');
            }

            $payment = CustomerPayment::create([
                'customer_id' => $customer->id,
                'sale_id' => $saleModel?->id,
                'amount' => $amount,
                'payment_method' => $paymentMethod,
                'paid_at' => now(),
                'recorded_by' => $recordedBy,
                'notes' => $notes,
            ]);

            if ($saleModel) {
                $saleModel->paid_amount = round((float) $saleModel->paid_amount + $amount, 2);
                $saleModel->status = $saleModel->paid_amount >= $saleModel->total_amount ? 'paid' : 'partial';
                $saleModel->save();
            }

            return $payment;
        });
    }
}
