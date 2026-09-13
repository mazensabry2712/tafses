<x-layouts.app title="حساب العميل">
    <main dir="rtl" class="mx-auto max-w-7xl space-y-6 px-6 py-8">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div><h1 class="text-2xl font-bold">{{ $customer->name }}</h1><p class="mt-1 text-sm text-gray-600">{{ $customer->phone }} {{ $customer->address ? '— '.$customer->address : '' }}</p></div>
            <a href="{{ route('sales.index') }}" class="rounded-lg border bg-white px-4 py-2 text-sm font-semibold">العودة للمبيعات</a>
        </div>
        <section class="grid gap-4 sm:grid-cols-3">
            <div class="rounded-2xl border bg-white p-5 shadow-sm"><div class="text-sm text-gray-500">إجمالي المبيعات</div><div class="mt-2 text-2xl font-bold">{{ number_format($balance['sales_total'], 2) }} ج.م</div></div>
            <div class="rounded-2xl border bg-white p-5 shadow-sm"><div class="text-sm text-gray-500">إجمالي المدفوع</div><div class="mt-2 text-2xl font-bold">{{ number_format($balance['paid_total'], 2) }} ج.م</div></div>
            <div class="rounded-2xl border bg-white p-5 shadow-sm"><div class="text-sm text-gray-500">الرصيد المستحق</div><div class="mt-2 text-2xl font-bold">{{ number_format($balance['balance_due'], 2) }} ج.م</div></div>
        </section>
        @canPermission('manage_payments')
        <section class="rounded-2xl border bg-white p-6 shadow-sm">
            <h2 class="text-lg font-bold">تحصيل دفعة</h2>
            <form method="POST" class="mt-5 grid gap-4 md:grid-cols-4" id="payment-form">
                @csrf
                <select name="sale_id" required class="rounded-lg border-gray-300" onchange="document.getElementById('payment-form').action='{{ url('/sales/customers/'.$customer->id.'/invoices') }}/'+this.value+'/payments'">
                    <option value="">اختر الفاتورة</option>
                    @foreach ($sales->filter(fn ($sale) => $sale->balance() > 0) as $sale)
                        <option value="{{ $sale->id }}">{{ $sale->invoice_number }} — متبقي {{ number_format($sale->balance(), 2) }} ج.م</option>
                    @endforeach
                </select>
                <input name="amount" type="number" min="0.01" step="0.01" required placeholder="المبلغ" class="rounded-lg border-gray-300">
                <select name="payment_method" class="rounded-lg border-gray-300"><option value="cash">نقدي</option><option value="bank">تحويل بنكي</option><option value="other">أخرى</option></select>
                <button class="rounded-lg bg-gray-900 px-4 py-2 font-semibold text-white">تسجيل التحصيل</button>
            </form>
        </section>
        @endcanPermission
        <section class="rounded-2xl border bg-white p-6 shadow-sm">
            <h2 class="text-lg font-bold">فواتير العميل</h2>
            <div class="mt-5 overflow-x-auto"><table class="min-w-full text-right text-sm"><thead class="border-b text-gray-500"><tr><th class="px-3 py-3">الفاتورة</th><th class="px-3 py-3">التاريخ</th><th class="px-3 py-3">الإجمالي</th><th class="px-3 py-3">المدفوع</th><th class="px-3 py-3">المتبقي</th><th class="px-3 py-3">الحالة</th></tr></thead><tbody>
            @forelse ($sales as $sale)<tr class="border-b last:border-0"><td class="px-3 py-3 font-semibold">{{ $sale->invoice_number }}</td><td class="px-3 py-3">{{ $sale->sold_at?->format('Y-m-d H:i') }}</td><td class="px-3 py-3">{{ number_format((float) $sale->total_amount, 2) }}</td><td class="px-3 py-3">{{ number_format((float) $sale->paid_amount, 2) }}</td><td class="px-3 py-3">{{ number_format($sale->balance(), 2) }}</td><td class="px-3 py-3">{{ $sale->status }}</td></tr>@empty<tr><td colspan="6" class="px-3 py-8 text-center text-gray-500">لا توجد فواتير.</td></tr>@endforelse
            </tbody></table></div>
        </section>
    </main>
</x-layouts.app>
