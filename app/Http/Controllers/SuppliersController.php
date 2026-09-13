<?php

namespace App\Http\Controllers;

use App\Actions\CreatePomegranatePurchaseAction;
use App\Actions\GetSupplierBalanceAction;
use App\Actions\RecordSupplierPaymentAction;
use App\Models\PomegranateLoad;
use App\Models\PomegranatePurchase;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SuppliersController extends Controller
{
    public function index(GetSupplierBalanceAction $balanceAction)
    {
        $suppliers = Supplier::query()
            ->orderBy('name')
            ->get()
            ->map(function (Supplier $supplier) use ($balanceAction) {
                $balance = $balanceAction->execute($supplier);
                return compact('supplier', 'balance');
            });

        return view('suppliers.index', [
            'suppliers' => $suppliers,
            'loads' => PomegranateLoad::query()
                ->with('supplier')
                ->whereNotNull('supplier_id')
                ->whereDoesntHave('purchase')
                ->orderByDesc('received_at')
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        Supplier::create([
            ...$data,
            'is_active' => true,
        ]);

        return back()->with('success', 'تم إضافة المورد بنجاح.');
    }

    public function show(Supplier $supplier, GetSupplierBalanceAction $balanceAction)
    {
        return view('suppliers.show', [
            'supplier' => $supplier,
            'balance' => $balanceAction->execute($supplier),
            'purchases' => $supplier->pomegranatePurchases()
                ->with('pomegranateLoad:id,load_number')
                ->latest('purchased_at')
                ->latest('id')
                ->get(),
        ]);
    }

    public function purchaseStore(Request $request, Supplier $supplier, CreatePomegranatePurchaseAction $action): RedirectResponse
    {
        $data = $request->validate([
            'pomegranate_load_id' => ['required', 'integer', 'exists:pomegranate_loads,id'],
            'pricing_unit' => ['required', 'in:kg,crate,fixed'],
            'unit_price' => ['required', 'numeric', 'gt:0'],
            'quantity' => ['nullable', 'numeric', 'gt:0'],
            'initial_paid' => ['nullable', 'numeric', 'gte:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $load = PomegranateLoad::query()->findOrFail($data['pomegranate_load_id']);
        abort_unless((int) $load->supplier_id === (int) $supplier->id, 422, 'الحمولة لا تتبع هذا المورد.');

        $action->execute(
            $load,
            $data['pricing_unit'],
            (float) $data['unit_price'],
            isset($data['quantity']) ? (float) $data['quantity'] : null,
            isset($data['initial_paid']) ? (float) $data['initial_paid'] : null,
            $data['notes'] ?? null,
        );

        return back()->with('success', 'تم تسجيل شراء الحمولة بنجاح.');
    }

    public function paymentStore(Request $request, Supplier $supplier, PomegranatePurchase $purchase, RecordSupplierPaymentAction $action): RedirectResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'gt:0'],
            'payment_method' => ['required', 'in:cash,bank,transfer,other'],
            'notes' => ['nullable', 'string'],
        ]);

        abort_unless((int) $purchase->supplier_id === (int) $supplier->id, 422, 'الفاتورة لا تتبع هذا المورد.');

        $action->execute(
            $purchase,
            (float) $data['amount'],
            $data['payment_method'],
            $data['notes'] ?? null,
        );

        return back()->with('success', 'تم تسجيل دفعة المورد بنجاح.');
    }
}
