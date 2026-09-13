<x-layouts.app title="المبيعات والعملاء">
    <main dir="rtl" class="mx-auto max-w-7xl space-y-6 px-6 py-8">
        <div>
            <h1 class="text-2xl font-bold">المبيعات والعملاء</h1>
            <p class="mt-1 text-sm text-gray-600">إنشاء الفواتير ومتابعة أرصدة العملاء والتحصيل.</p>
        </div>

        @if ($errors->any())
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                <ul class="list-disc space-y-1 pr-5">
                    @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                </ul>
            </div>
        @endif

        @canPermission('manage_sales')
        <section class="rounded-2xl border bg-white p-6 shadow-sm">
            <h2 class="text-lg font-bold">فاتورة بيع جديدة</h2>
            <form method="POST" action="{{ route('sales.store', $customers->first() ?? 0) }}" class="mt-5 space-y-4" id="sale-form">
                @csrf
                <div class="grid gap-4 md:grid-cols-3">
                    <select name="customer_id" required class="rounded-lg border-gray-300" onchange="document.getElementById('sale-form').action='{{ url('/sales/customers') }}/'+this.value+'/invoices'">
                        <option value="">اختر العميل</option>
                        @foreach ($customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->name }} — {{ $customer->phone }}</option>
                        @endforeach
                    </select>
                    <input name="invoice_number" value="{{ old('invoice_number') }}" required placeholder="رقم الفاتورة" class="rounded-lg border-gray-300">
                    <input name="paid_amount" type="number" min="0" step="0.01" value="0" placeholder="المدفوع" class="rounded-lg border-gray-300">
                </div>
                <div class="overflow-x-auto rounded-lg border">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-right"><tr><th class="px-3 py-3">المنتج</th><th class="px-3 py-3">الكمية</th><th class="px-3 py-3">سعر الوحدة</th></tr></thead>
                        <tbody><tr>
                            <td class="px-3 py-3"><select name="items[0][finished_product_id]" required class="w-full rounded-lg border-gray-300"><option value="">اختر المنتج</option>@foreach ($products as $product)<option value="{{ $product->id }}">{{ $product->name }} (متاح {{ number_format((float) $product->stock->quantity, 3) }} {{ $product->unit }})</option>@endforeach</select></td>
                            <td class="px-3 py-3"><input name="items[0][quantity]" type="number" min="0.001" step="0.001" required class="w-full rounded-lg border-gray-300"></td>
                            <td class="px-3 py-3"><input name="items[0][unit_price]" type="number" min="0" step="0.01" required class="w-full rounded-lg border-gray-300"></td>
                        </tr></tbody>
                    </table>
                </div>
                <textarea name="notes" rows="2" placeholder="ملاحظات" class="w-full rounded-lg border-gray-300"></textarea>
                <button class="rounded-lg bg-gray-900 px-5 py-2.5 font-semibold text-white hover:bg-gray-700">حفظ الفاتورة</button>
            </form>
            @if ($customers->isEmpty())<p class="mt-3 text-sm text-amber-700">لا يوجد عملاء نشطون. أضف عميلًا من إدارة العملاء أولًا.</p>@endif
        </section>
        @endcanPermission

        <section class="rounded-2xl border bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between"><div><h2 class="text-lg font-bold">آخر الفواتير</h2><p class="text-sm text-gray-500">آخر 30 فاتورة</p></div></div>
            <div class="mt-5 overflow-x-auto">
                <table class="min-w-full text-right text-sm">
                    <thead class="border-b text-gray-500"><tr><th class="px-3 py-3">الفاتورة</th><th class="px-3 py-3">العميل</th><th class="px-3 py-3">الإجمالي</th><th class="px-3 py-3">المدفوع</th><th class="px-3 py-3">المتبقي</th><th class="px-3 py-3">الحالة</th></tr></thead>
                    <tbody>
                    @forelse ($sales as $sale)
                        <tr class="border-b last:border-0"><td class="px-3 py-3 font-semibold">{{ $sale->invoice_number }}</td><td class="px-3 py-3"><a class="underline" href="{{ route('sales.customer', $sale->customer) }}">{{ $sale->customer->name }}</a></td><td class="px-3 py-3">{{ number_format((float) $sale->total_amount, 2) }}</td><td class="px-3 py-3">{{ number_format((float) $sale->paid_amount, 2) }}</td><td class="px-3 py-3">{{ number_format($sale->balance(), 2) }}</td><td class="px-3 py-3">{{ $sale->status }}</td></tr>
                    @empty
                        <tr><td colspan="6" class="px-3 py-8 text-center text-gray-500">لا توجد فواتير حتى الآن.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</x-layouts.app>
