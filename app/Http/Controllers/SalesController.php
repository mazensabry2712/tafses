<?php

namespace App\Http\Controllers;

use App\Actions\CreateFinishedProductSaleAction;
use App\Actions\GetCustomerBalanceAction;
use App\Actions\RecordCustomerPaymentAction;
use App\Models\Customer;
use App\Models\FinishedProduct;
use App\Models\FinishedProductSale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SalesController extends Controller
{
    public function index()
    {
        $customers = Customer::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $products = FinishedProduct::query()
            ->with('stock')
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->filter(fn (FinishedProduct $product) => (float) ($product->stock?->quantity ?? 0) > 0)
            ->values();

        $sales = FinishedProductSale::query()
            ->with('customer')
            ->withCount('items')
            ->orderByDesc('sold_at')
            ->limit(30)
            ->get();

        return view('sales.index', compact('customers', 'products', 'sales'));
    }

    public function customer(Customer $customer, GetCustomerBalanceAction $balanceAction)
    {
        abort_unless($customer->is_active, 404);

        return view('sales.customer', [
            'customer' => $customer,
            'balance' => $balanceAction->execute($customer),
            'sales' => $customer->sales()->with('items.finishedProduct')->orderByDesc('sold_at')->get(),
        ]);
    }

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

        return back()->with('success', 'تم إنشاء فاتورة البيع بنجاح.');
    }

    public function payment(Request $request, Customer $customer, FinishedProductSale $sale, RecordCustomerPaymentAction $action): RedirectResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'gt:0'],
            'payment_method' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string'],
        ]);

        abort_unless($sale->customer_id === $customer->id, 404);

        $action->execute(
            $customer,
            (float) $data['amount'],
            $sale,
            $data['payment_method'] ?? null,
            $request->user()->id,
            $data['notes'] ?? null,
        );

        return back()->with('success', 'تم تسجيل تحصيل العميل بنجاح.');
    }
}
