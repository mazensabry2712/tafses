<x-layouts.app title="الموردون والمشتريات">
    <main dir="rtl" class="mx-auto max-w-7xl space-y-6 px-4 py-5 sm:px-6 sm:py-7 lg:px-8">
        <section class="overflow-hidden rounded-3xl bg-gradient-to-br from-slate-950 via-emerald-950 to-emerald-900 p-5 text-white shadow-xl sm:p-7">
            <div>
                <div class="inline-flex rounded-full bg-white/10 px-3 py-1 text-xs font-semibold text-emerald-100 ring-1 ring-white/10">الحسابات / الموردون</div>
                <h1 class="mt-3 text-2xl font-black tracking-tight sm:text-3xl">الموردون والمشتريات</h1>
                <p class="mt-2 text-sm leading-6 text-emerald-50/80 sm:text-base">إدارة الموردين، شراء حمولات الرمان، ومتابعة المستحقات.</p>
            </div>
        </section>

        <div class="grid gap-6 xl:grid-cols-3">
            <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <div>
                    <h2 class="text-xl font-black text-slate-900">إضافة مورد</h2>
                    <p class="mt-1 text-sm text-slate-500">أضف بيانات المورد الأساسية للمتابعة المالية.</p>
                </div>
                <form method="POST" action="{{ route('suppliers.store') }}" class="mt-5 space-y-3">
                    @csrf
                    <input name="name" required placeholder="اسم المورد" class="h-12 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm outline-none focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/10">
                    <input name="phone" placeholder="الهاتف" inputmode="tel" class="h-12 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm outline-none focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/10">
                    <input name="address" placeholder="العنوان" class="h-12 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm outline-none focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/10">
                    <textarea name="notes" rows="3" placeholder="ملاحظات" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/10"></textarea>
                    <button class="min-h-12 w-full rounded-xl bg-emerald-700 px-4 py-3 text-sm font-black text-white transition hover:bg-emerald-800">حفظ المورد</button>
                </form>
            </section>

            <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm xl:col-span-2">
                <div class="border-b border-slate-100 px-4 py-5 sm:px-6">
                    <h2 class="text-xl font-black text-slate-900">رصيد الموردين</h2>
                    <p class="mt-1 text-sm text-slate-500">إجمالي المشتريات والمدفوع والمستحق لكل مورد.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-[720px] w-full text-right text-sm">
                        <thead class="bg-slate-50 text-slate-500"><tr><th class="px-4 py-3 font-bold sm:px-6">المورد</th><th class="px-4 py-3 font-bold">إجمالي المشتريات</th><th class="px-4 py-3 font-bold">المدفوع</th><th class="px-4 py-3 font-bold">المستحق</th></tr></thead>
                        <tbody class="divide-y divide-slate-100">
                        @forelse ($suppliers as $row)
                            <tr class="transition hover:bg-slate-50">
                                <td class="px-4 py-4 sm:px-6"><a class="font-bold text-emerald-700 hover:underline" href="{{ route('suppliers.show', $row['supplier']) }}">{{ $row['supplier']->name }}</a></td>
                                <td class="px-4 py-4 text-slate-600">{{ number_format($row['balance']['purchase_total'], 2) }} ج.م</td>
                                <td class="px-4 py-4 text-slate-600">{{ number_format($row['balance']['paid_total'], 2) }} ج.م</td>
                                <td class="px-4 py-4 font-black text-slate-900">{{ number_format($row['balance']['balance_due'], 2) }} ج.م</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-10 text-center text-slate-500">لا يوجد موردون حتى الآن.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-4 py-5 sm:px-6">
                <h2 class="text-xl font-black text-slate-900">تسجيل شراء حمولة</h2>
                <p class="mt-1 text-sm leading-6 text-slate-500">لا تظهر إلا الحمولات التي لها مورد ولم يتم تسجيل شراء لها.</p>
            </div>
            <div class="p-4 sm:p-6">
                @forelse ($loads as $load)
                    <form method="POST" action="{{ route('suppliers.purchases.store', $load->supplier) }}" class="mb-4 rounded-2xl border border-slate-200 bg-slate-50/70 p-4 last:mb-0 sm:p-5">
                        @csrf
                        <input type="hidden" name="pomegranate_load_id" value="{{ $load->id }}">
                        <div class="mb-4 flex flex-col gap-2 border-b border-slate-200 pb-4 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <div class="text-base font-black text-slate-900">{{ $load->load_number }}</div>
                                <div class="mt-1 text-xs text-slate-500">{{ $load->supplier->name }} — {{ number_format($load->loaded_crates_count) }} قفص / {{ number_format($load->loaded_weight_kg, 1) }} كجم</div>
                            </div>
                            <span class="rounded-xl bg-white px-3 py-2 text-xs font-bold text-slate-600 ring-1 ring-slate-200">شراء حمولة</span>
                        </div>
                        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
                            <select name="pricing_unit" class="h-12 rounded-xl border border-slate-200 bg-white px-4 text-sm outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10" required>
                                <option value="kg">بالكيلو</option><option value="crate">بالقفص</option><option value="fixed">مبلغ ثابت</option>
                            </select>
                            <input name="unit_price" type="number" step="0.001" min="0.001" required placeholder="السعر" inputmode="decimal" class="h-12 rounded-xl border border-slate-200 bg-white px-4 text-sm outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10">
                            <input name="quantity" type="number" step="0.001" min="0.001" placeholder="الكمية (اختياري)" inputmode="decimal" class="h-12 rounded-xl border border-slate-200 bg-white px-4 text-sm outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10">
                            <input name="initial_paid" type="number" step="0.001" min="0" placeholder="دفعة أولى" inputmode="decimal" class="h-12 rounded-xl border border-slate-200 bg-white px-4 text-sm outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10">
                            <button class="min-h-12 rounded-xl bg-emerald-700 px-4 py-3 text-sm font-black text-white transition hover:bg-emerald-800">حفظ الشراء</button>
                        </div>
                    </form>
                @empty
                    <div class="rounded-2xl bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">لا توجد حمولات جاهزة لتسجيل شراء.</div>
                @endforelse
            </div>
        </section>

        @if ($errors->any())
            <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-4 text-sm text-red-800 shadow-sm"><ul class="list-disc space-y-1 pr-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif
    </main>
</x-layouts.app>
