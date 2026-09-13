<?php

namespace App\Http\Controllers;

use App\Actions\GetCustomerBalanceAction;
use App\Actions\GetSupplierBalanceAction;
use App\Models\Customer;
use App\Models\Supplier;

class AccountingController extends Controller
{
    public function index(GetCustomerBalanceAction $customerBalance, GetSupplierBalanceAction $supplierBalance)
    {
        $customers = Customer::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn (Customer $customer) => [
                'customer' => $customer,
                'balance' => $customerBalance->execute($customer),
            ]);

        $suppliers = Supplier::query()
            ->orderBy('name')
            ->get()
            ->map(fn (Supplier $supplier) => [
                'supplier' => $supplier,
                'balance' => $supplierBalance->execute($supplier),
            ]);

        return view('accounting.index', compact('customers', 'suppliers'));
    }
}
