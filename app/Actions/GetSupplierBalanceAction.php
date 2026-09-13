<?php

namespace App\Actions;

use App\Models\Supplier;

class GetSupplierBalanceAction
{
    public function execute(Supplier $supplier): array
    {
        $purchaseTotal = (float) $supplier->pomegranatePurchases()->sum('total_amount');
        $purchasePaid = (float) $supplier->pomegranatePurchases()->sum('paid_amount');
        $unlinkedPayments = (float) $supplier->payments()
            ->whereNull('pomegranate_purchase_id')
            ->sum('amount');

        $paidTotal = $purchasePaid + $unlinkedPayments;

        return [
            'supplier_id' => $supplier->id,
            'supplier_name' => $supplier->name,
            'purchase_total' => round($purchaseTotal, 3),
            'paid_total' => round($paidTotal, 3),
            'balance_due' => round(max(0, $purchaseTotal - $paidTotal), 3),
        ];
    }
}
