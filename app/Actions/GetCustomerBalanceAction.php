<?php

namespace App\Actions;

use App\Models\Customer;

class GetCustomerBalanceAction
{
    public function execute(Customer $customer): array
    {
        $salesTotal = (float) $customer->sales()->sum('total_amount');
        $paidTotal = (float) $customer->payments()->sum('amount');

        return [
            'customer' => $customer->name,
            'sales_total' => round($salesTotal, 2),
            'paid_total' => round($paidTotal, 2),
            'balance_due' => round($salesTotal - $paidTotal, 2),
        ];
    }
}
