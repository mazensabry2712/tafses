<x-layouts.app title="المبيعات والعملاء">
    <main dir="rtl" class="mx-auto max-w-7xl space-y-6 px-4 py-5 sm:px-6 sm:py-7 lg:px-8">
        <section class="overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-950 via-emerald-900 to-slate-900 p-5 text-white shadow-xl sm:p-7">
            <div>
                <div class="inline-flex rounded-full bg-white/10 px-3 py-1 text-xs font-semibold text-emerald-100 ring-1 ring-white/10">الحسابات / المبيعات</div>
                <h1 class="mt-3 text-2xl font-black tracking-tight sm:text-3xl">المبيعات والعملاء</h1>
                <p class="mt-2 text-sm leading-6 text-emerald-50/80 sm:text-base">إنشاء الفواتير ومتابعة أرصدة العملاء والتحصيل.</p>
            </div>
        </section>

        @if ($errors->any())
            <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-4 text-sm text-red-800 shadow-sm">
                <ul class="list-disc space-y-1 pr-5">
                    @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                </ul>
            </div>
        @endif

        @canPermission('manage_sales')
        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-4 py-5 sm:px-6">
                <h2 class="text-xl font-black text-slate-900">فاتورة بيع جديدة</h2>
                <p class="mt-1 text-sm leading-6 text-slate-500">اختر العميل والمنتج، ثم سجل المبلغ المدفوع عند إنشاء الفاتورة.</p>
            </div>
            <form method="POST" action="{{ route('sales.store', $customers->first() ?? 0) }}" class="space-y-5 p-4 sm:p-6" id="sale-form">
                @csrf
                <div class="grid gap-4 md:grid-cols-3">
                    <div class="md:col-span-1">
                        <label class="block text-sm font-bold text-slate-700">العميل</label>
                        <select name="customer_id" required onchange="document.getElementById('sale-form').action='{{ url('/sales/customers') }}/'+this.value+'/invoices'" class="mt-2 h-12 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm outline-none transition focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/10">
                            <option value="">اختر العميل</option>
                            @foreach ($customers as $customer)
                                <option value="{{ $customer->id }}">{{ $customer->name }} — {{ $customer->phone }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700">رقم الفاتورة</label>
                        <input name="invoice_number" value="{{ old('invoice_number') }}" required placeholder="رقم الفاتورة" class="mt-2 h-12 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm outline-none transition focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/10">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700">المدفوع</label>
                        <input name="paid_amount" type="number" min="0" step="0.01" value="0" placeholder="المدفوع" inputmode="decimal" class="mt-2 h-12 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm outline-none transition focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/10">
                    </div>
                </div>

                <div class="overflow-x-auto rounded-2xl border border-slate-200">
                    <table class="min-w-[680px] w-full text-right text-sm">
                        <thead class="bg-slate-50 text-slate-500">
                            <tr><th class="px-4 py-3 font-bold">المنتج</th><th class="px-4 py-3 font-bold">الكمية</th><th class="px-4 py-3 font-bold">سعر الوحدة</th></tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="px-4 py-4">
                                    <select name="items[0][finished_product_id]" required class="h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10">
                                        <option value="">اختر المنتج</option>
                                        @foreach ($products as $product)
                                            <option value="{{ $product->id }}">{{ $product->name }} (متاح {{ number_format((float) $product->stock->quantity, 3) }} {{ $product->unit }})</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="px-4 py-4"><input name="items[0][quantity]" type="number" min="0.001" step="0.001" required inputmode="decimal" class="h-11 w-full rounded-xl border border-slate-200 px-3 text-sm outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10"></td>
                                <td class="px-4 py-4"><input name="items[0][unit_price]" type="number" min="0" step="0.01" required inputmode="decimal" class="h-11 w-full rounded-xl border border-slate-200 px-3 text-sm outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700">ملاحظات</label>
                    <textarea name="notes" rows="3" placeholder="ملاحظات الفاتورة (اختياري)" class="mt-2 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/10"></textarea>
                </div>
                <div class="flex justify-end border-t border-slate-100 pt-5">
                    <button class="min-h-12 w-full rounded-xl bg-emerald-700 px-6 py-3 text-sm font-black text-white transition hover:bg-emerald-800 sm:w-auto">حفظ الفاتورة</button>
                </div>
            </form>
            @if ($customers->isEmpty())<p class="px-4 pb-5 text-sm font-semibold text-amber-700 sm:px-6">لا يوجد عملاء نشطون. أضف عميلًا من إدارة العملاء أولًا.</p>@endif
        </section>
        @endcanPermission

        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="px-4 py-5 sm:px-6">
                <h2 class="text-xl font-black text-slate-900">آخر الفواتير</h2>
                <p class="mt-1 text-sm text-slate-500">آخر 30 فاتورة</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-[780px] w-full text-right text-sm">
                    <thead class="bg-slate-50 text-slate-500">
                        <tr><th class="px-4 py-3 font-bold sm:px-6">الفاتورة</th><th class="px-4 py-3 font-bold">العميل</th><th class="px-4 py-3 font-bold">الإجمالي</th><th class="px-4 py-3 font-bold">المدفوع</th><th class="px-4 py-3 font-bold">المتبقي</th><th class="px-4 py-3 font-bold">الحالة</th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                    @forelse ($sales as $sale)
                        <tr class="transition hover:bg-slate-50">
                            <td class="px-4 py-4 font-bold text-slate-900 sm:px-6">{{ $sale->invoice_number }}</td>
                            <td class="px-4 py-4"><a class="font-semibold text-emerald-700 hover:underline" href="{{ route('sales.customer', $sale->customer) }}">{{ $sale->customer->name }}</a></td>
                            <td class="px-4 py-4 text-slate-600">{{ number_format((float) $sale->total_amount, 2) }}</td>
                            <td class="px-4 py-4 text-slate-600">{{ number_format((float) $sale->paid_amount, 2) }}</td>
                            <td class="px-4 py-4 font-bold text-slate-900">{{ number_format($sale->balance(), 2) }}</td>
                            <td class="px-4 py-4"><span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">{{ $sale->status }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-10 text-center text-slate-500">لا توجد فواتير حتى الآن.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</x-layouts.app>
