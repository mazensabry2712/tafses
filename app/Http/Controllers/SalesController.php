<?php

namespace App\Http\Controllers;

use App\Actions\CreateFinishedProductSaleAction;
use App\Actions\RecordCustomerPaymentAction;
use App\Models\Customer;
use App\Models\FinishedProductSale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SalesController extends Controller
{
    public function store(Request $request, Customer $customer, CreateFinishedProductSaleAction $action): RedirectResponse
    {
        $data = $request->validate([
            'invoice_number' => ['required', 'string', 'max:100'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.finished_product_id' => ['required', 'integer', 'exists:finished_products,id'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.unit_price' => ['required', 'numeric', 'gte:0'],
            'paid_amount' => ['nullable', 'numeric', 'gte:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $action->execute(
            $customer,
            $data['items'],
            $data['invoice_number'],
            (float) ($data['paid_amount'] ?? 0),
            $request->user()->id,
            $data['notes'] ?? null,
        );

        return back()->with('success', 'Sales invoice created successfully.');
    }

    public function payment(Request $request, Customer $customer, FinishedProductSale $sale, RecordCustomerPaymentAction $action): RedirectResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'gt:0'],
            'payment_method' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string'],
        ]);

        $action->execute(
            $customer,
            (float) $data['amount'],
            $sale,
            $data['payment_method'] ?? null,
            $request->user()->id,
            $data['notes'] ?? null,
        );

        return back()->with('success', 'Customer payment recorded successfully.');
    }
}
