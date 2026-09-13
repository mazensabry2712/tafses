<x-layouts.app title="حساب المورد">
    <main dir="rtl" class="mx-auto max-w-7xl space-y-6 px-6 py-8">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div><p class="text-sm text-gray-500">حساب المورد</p><h1 class="mt-1 text-2xl font-bold">{{ $supplier->name }}</h1><p class="mt-1 text-sm text-gray-500">{{ $supplier->phone ?? 'بدون هاتف' }}</p></div>
            <a href="{{ route('suppliers.index') }}" class="rounded-lg border px-4 py-2 text-sm font-semibold hover:bg-gray-50">العودة للموردين</a>
        </div>

        <section class="grid gap-4 sm:grid-cols-3">
            <div class="rounded-2xl border bg-white p-5 shadow-sm"><div class="text-sm text-gray-500">المشتريات</div><div class="mt-2 text-2xl font-bold">{{ number_format($balance['purchase_total'], 2) }} ج.م</div></div>
            <div class="rounded-2xl border bg-white p-5 shadow-sm"><div class="text-sm text-gray-500">المدفوع</div><div class="mt-2 text-2xl font-bold">{{ number_format($balance['paid_total'], 2) }} ج.م</div></div>
            <div class="rounded-2xl border bg-white p-5 shadow-sm"><div class="text-sm text-gray-500">المستحق</div><div class="mt-2 text-2xl font-bold">{{ number_format($balance['balance_due'], 2) }} ج.م</div></div>
        </section>

        <section class="rounded-2xl border bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold">المشتريات والدفعات</h2>
            <div class="mt-5 overflow-x-auto">
                <table class="min-w-full text-right text-sm">
                    <thead class="border-b text-gray-500"><tr><th class="px-3 py-3">الحمولة</th><th class="px-3 py-3">الوحدة</th><th class="px-3 py-3">الكمية</th><th class="px-3 py-3">الإجمالي</th><th class="px-3 py-3">المدفوع</th><th class="px-3 py-3">المتبقي</th><th class="px-3 py-3">تحصيل</th></tr></thead>
                    <tbody>
                    @forelse ($purchases as $purchase)
                        <tr class="border-b last:border-0">
                            <td class="px-3 py-3 font-semibold">{{ $purchase->pomegranateLoad?->load_number ?? '-' }}</td>
                            <td class="px-3 py-3">{{ $purchase->pricing_unit }}</td>
                            <td class="px-3 py-3">{{ number_format($purchase->quantity, 3) }}</td>
                            <td class="px-3 py-3">{{ number_format($purchase->total_amount, 2) }}</td>
                            <td class="px-3 py-3">{{ number_format($purchase->paid_amount, 2) }}</td>
                            <td class="px-3 py-3 font-semibold">{{ number_format($purchase->balance(), 2) }}</td>
                            <td class="px-3 py-3">
                                @if ($purchase->balance() > 0)
                                <form method="POST" action="{{ route('suppliers.purchases.payments.store', [$supplier, $purchase]) }}" class="flex min-w-[260px] gap-2">
                                    @csrf
                                    <input name="amount" type="number" step="0.001" min="0.001" max="{{ $purchase->balance() }}" required placeholder="المبلغ" class="w-28 rounded-lg border-gray-300">
                                    <select name="payment_method" class="rounded-lg border-gray-300"><option value="cash">نقدي</option><option value="bank">بنك</option><option value="transfer">تحويل</option><option value="other">أخرى</option></select>
                                    <button class="rounded-lg bg-gray-900 px-3 py-2 text-xs font-medium text-white">سداد</button>
                                </form>
                                @else <span class="text-sm text-green-700">مسدد بالكامل</span> @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-3 py-8 text-center text-gray-500">لا توجد مشتريات لهذا المورد.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        @if ($errors->any())
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800"><ul class="list-disc pr-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif
    </main>
</x-layouts.app>
