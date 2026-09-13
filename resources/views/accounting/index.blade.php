<x-layouts.app title="الحسابات">
    <main dir="rtl" class="mx-auto max-w-7xl space-y-6 px-4 py-5 sm:px-6 sm:py-7 lg:px-8">
        <section class="overflow-hidden rounded-3xl bg-gradient-to-br from-slate-950 via-emerald-950 to-emerald-900 p-5 text-white shadow-xl sm:p-7">
            <div class="inline-flex rounded-full bg-white/10 px-3 py-1 text-xs font-semibold text-emerald-100 ring-1 ring-white/10">الحسابات</div>
            <h1 class="mt-3 text-2xl font-black tracking-tight sm:text-3xl">الحسابات</h1>
            <p class="mt-2 text-sm leading-6 text-emerald-50/80 sm:text-base">أرصدة العملاء والموردين ومتابعة المستحقات.</p>
        </section>

        <section class="grid gap-6 xl:grid-cols-2">
            <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-4 py-5 sm:px-6"><h2 class="text-xl font-black text-slate-900">حسابات العملاء</h2></div>
                <div class="overflow-x-auto">
                    <table class="min-w-[700px] w-full text-right text-sm">
                        <thead class="bg-slate-50 text-slate-500"><tr><th class="px-4 py-3 font-bold sm:px-6">العميل</th><th class="px-4 py-3 font-bold">المبيعات</th><th class="px-4 py-3 font-bold">المدفوع</th><th class="px-4 py-3 font-bold">المتبقي</th></tr></thead>
                        <tbody class="divide-y divide-slate-100">
                        @forelse ($customers as $row)
                            <tr class="transition hover:bg-slate-50"><td class="px-4 py-4 font-bold sm:px-6"><a href="{{ route('sales.customer', $row['customer']) }}" class="text-emerald-700 hover:underline">{{ $row['customer']->name }}</a></td><td class="px-4 py-4 text-slate-600">{{ number_format($row['balance']['sales_total'], 2) }} ج.م</td><td class="px-4 py-4 text-slate-600">{{ number_format($row['balance']['paid_total'], 2) }} ج.م</td><td class="px-4 py-4 font-black text-slate-900">{{ number_format($row['balance']['balance_due'], 2) }} ج.م</td></tr>
                        @empty <tr><td colspan="4" class="px-4 py-10 text-center text-slate-500">لا يوجد عملاء نشطون.</td></tr>@endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-4 py-5 sm:px-6"><h2 class="text-xl font-black text-slate-900">حسابات الموردين</h2></div>
                <div class="overflow-x-auto">
                    <table class="min-w-[700px] w-full text-right text-sm">
                        <thead class="bg-slate-50 text-slate-500"><tr><th class="px-4 py-3 font-bold sm:px-6">المورد</th><th class="px-4 py-3 font-bold">المشتريات</th><th class="px-4 py-3 font-bold">المدفوع</th><th class="px-4 py-3 font-bold">المتبقي</th></tr></thead>
                        <tbody class="divide-y divide-slate-100">
                        @forelse ($suppliers as $row)
                            <tr class="transition hover:bg-slate-50"><td class="px-4 py-4 font-bold sm:px-6"><a href="{{ route('suppliers.show', $row['supplier']) }}" class="text-emerald-700 hover:underline">{{ $row['supplier']->name }}</a></td><td class="px-4 py-4 text-slate-600">{{ number_format($row['balance']['purchase_total'], 2) }} ج.م</td><td class="px-4 py-4 text-slate-600">{{ number_format($row['balance']['paid_total'], 2) }} ج.م</td><td class="px-4 py-4 font-black text-slate-900">{{ number_format($row['balance']['balance_due'], 2) }} ج.م</td></tr>
                        @empty <tr><td colspan="4" class="px-4 py-10 text-center text-slate-500">لا يوجد موردون.</td></tr>@endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>
</x-layouts.app>
