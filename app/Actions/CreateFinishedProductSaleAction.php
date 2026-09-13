<?php

namespace App\Actions;

use App\Models\Customer;
use App\Models\CustomerPayment;
use App\Models\FinishedProduct;
use App\Models\FinishedProductSale;
use App\Models\FinishedProductStock;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class CreateFinishedProductSaleAction
{
    public function execute(Customer $customer, array $items, string $invoiceNumber, float $paidAmount = 0, ?int $recordedBy = null, ?string $notes = null): FinishedProductSale
    {
        if ($items === []) throw new InvalidArgumentException('A sale must contain at least one item.');
        if ($paidAmount < 0) throw new InvalidArgumentException('Paid amount cannot be negative.');

        return DB::transaction(function () use ($customer, $items, $invoiceNumber, $paidAmount, $recordedBy, $notes) {
            $customer = Customer::query()->lockForUpdate()->findOrFail($customer->id);
            if (!$customer->is_active) throw new RuntimeException('The customer is inactive.');

            $prepared = [];
            $subtotal = 0.0;
            foreach ($items as $item) {
                $productId = (int) ($item['finished_product_id'] ?? 0);
                $quantity = (float) ($item['quantity'] ?? 0);
                $unitPrice = (float) ($item['unit_price'] ?? 0);
                if ($productId < 1 || $quantity <= 0 || $unitPrice < 0) {
                    throw new InvalidArgumentException('Sale item quantities and prices are invalid.');
                }
                $product = FinishedProduct::query()->findOrFail($productId);
                if (!$product->is_active) throw new RuntimeException('The finished product is inactive.');
                $stock = FinishedProductStock::query()->where('finished_product_id', $product->id)->lockForUpdate()->first();
                $available = (float) ($stock?->quantity ?? 0);
                if ($quantity > $available) throw new RuntimeException("Not enough stock for finished product [{$product->code}].");
                $lineTotal = round($quantity * $unitPrice, 2);
                $subtotal += $lineTotal;
                $prepared[] = compact('product', 'stock', 'quantity', 'unitPrice', 'lineTotal');
            }

            $subtotal = round($subtotal, 2);
            if ($paidAmount > $subtotal) throw new InvalidArgumentException('Paid amount cannot exceed the invoice total.');
            $status = $paidAmount <= 0 ? 'unpaid' : ($paidAmount >= $subtotal ? 'paid' : 'partial');

            $sale = FinishedProductSale::create([
                'customer_id' => $customer->id,
                'invoice_number' => $invoiceNumber,
                'sold_at' => now(),
                'subtotal' => $subtotal,
                'total_amount' => $subtotal,
                'paid_amount' => $paidAmount,
                'status' => $status,
                'recorded_by' => $recordedBy,
                'notes' => $notes,
            ]);

            foreach ($prepared as $row) {
                $sale->items()->create([
                    'finished_product_id' => $row['product']->id,
                    'quantity' => $row['quantity'],
                    'unit_price' => $row['unitPrice'],
                    'total_amount' => $row['lineTotal'],
                ]);
                $row['stock']->quantity = round((float) $row['stock']->quantity - $row['quantity'], 3);
                $row['stock']->save();
                $row['product']->transactions()->create([
                    'type' => 'sale',
                    'quantity' => -$row['quantity'],
                    'moved_at' => now(),
                    'recorded_by' => $recordedBy,
                    'notes' => "Sale {$invoiceNumber}",
                ]);
            }

            if ($paidAmount > 0) {
                CustomerPayment::create([
                    'customer_id' => $customer->id,
                    'sale_id' => $sale->id,
                    'amount' => $paidAmount,
                    'paid_at' => now(),
                    'recorded_by' => $recordedBy,
                    'notes' => "Initial payment for sale {$invoiceNumber}",
                ]);
            }

            return $sale->load('items');
        });
    }
}
