<x-layouts.app title="التقارير التشغيلية">
    <main dir="rtl" class="mx-auto max-w-7xl space-y-6 px-4 py-5 sm:px-6 sm:py-7 lg:px-8">
        <section class="overflow-hidden rounded-3xl bg-gradient-to-br from-slate-950 via-emerald-950 to-emerald-900 p-5 text-white shadow-xl sm:p-7">
            <div class="inline-flex rounded-full bg-white/10 px-3 py-1 text-xs font-semibold text-emerald-100 ring-1 ring-white/10">التقارير والمتابعة</div>
            <h1 class="mt-3 text-2xl font-black tracking-tight sm:text-3xl">التقارير التشغيلية</h1>
            <p class="mt-2 max-w-3xl text-sm leading-6 text-emerald-50/80 sm:text-base">تحليل الاستلام والمشتريات والتصنيع والمبيعات والمخزون للفترة المحددة.</p>
        </section>

        <form method="GET" action="{{ route('reports.index') }}" class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm sm:p-6">
            <div class="grid gap-4 md:grid-cols-3">
                <label class="text-sm font-bold text-slate-700">من
                    <input type="date" name="from" value="{{ $from }}" class="mt-2 h-12 block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm outline-none focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/10">
                </label>
                <label class="text-sm font-bold text-slate-700">إلى
                    <input type="date" name="to" value="{{ $to }}" class="mt-2 h-12 block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm outline-none focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/10">
                </label>
                <div class="flex items-end"><button class="min-h-12 w-full rounded-xl bg-emerald-700 px-4 py-3 text-sm font-black text-white transition hover:bg-emerald-800">عرض التقرير</button></div>
            </div>
        </form>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><p class="text-sm font-bold text-slate-500">الشحنات</p><p class="mt-2 text-3xl font-black text-slate-900">{{ number_format($summary['receiving']['loads_count']) }}</p><p class="mt-1 text-sm text-slate-500">{{ number_format($summary['receiving']['crates_count']) }} قفص</p></div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><p class="text-sm font-bold text-slate-500">إجمالي الاستلام</p><p class="mt-2 text-3xl font-black text-slate-900">{{ number_format($summary['receiving']['weight_kg'], 1) }}</p><p class="mt-1 text-sm text-slate-500">كجم</p></div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><p class="text-sm font-bold text-slate-500">إنتاج التصنيع</p><p class="mt-2 text-3xl font-black text-slate-900">{{ number_format($summary['processing']['output_weight_kg'], 1) }}</p><p class="mt-1 text-sm text-slate-500">كجم</p></div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><p class="text-sm font-bold text-slate-500">المبيعات</p><p class="mt-2 text-3xl font-black text-slate-900">{{ number_format($summary['sales']['total_amount'], 2) }}</p><p class="mt-1 text-sm text-slate-500">ج.م</p></div>
        </section>

        <section class="grid gap-6 xl:grid-cols-2">
            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6"><h2 class="text-xl font-black text-slate-900">المشتريات والموردون</h2><dl class="mt-5 space-y-1"><div class="flex justify-between gap-4 rounded-xl bg-slate-50 px-4 py-3 text-sm"><dt class="text-slate-500">عدد المشتريات</dt><dd class="font-black text-slate-900">{{ number_format($summary['purchases']['count']) }}</dd></div><div class="flex justify-between gap-4 rounded-xl px-4 py-3 text-sm"><dt class="text-slate-500">إجمالي المشتريات</dt><dd class="font-black text-slate-900">{{ number_format($summary['purchases']['total_amount'], 2) }} ج.م</dd></div><div class="flex justify-between gap-4 rounded-xl bg-slate-50 px-4 py-3 text-sm"><dt class="text-slate-500">المدفوع</dt><dd class="font-black text-slate-900">{{ number_format($summary['purchases']['paid_amount'], 2) }} ج.م</dd></div><div class="flex justify-between gap-4 rounded-xl px-4 py-3 text-sm"><dt class="text-slate-500">المستحق</dt><dd class="font-black text-slate-900">{{ number_format($summary['purchases']['balance_due'], 2) }} ج.م</dd></div></dl></div>
            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6"><h2 class="text-xl font-black text-slate-900">التصنيع</h2><dl class="mt-5 space-y-1"><div class="flex justify-between gap-4 rounded-xl bg-slate-50 px-4 py-3 text-sm"><dt class="text-slate-500">دفعات التصنيع</dt><dd class="font-black text-slate-900">{{ number_format($summary['processing']['batches_count']) }}</dd></div><div class="flex justify-between gap-4 rounded-xl px-4 py-3 text-sm"><dt class="text-slate-500">الداخل</dt><dd class="font-black text-slate-900">{{ number_format($summary['processing']['input_weight_kg'], 1) }} كجم</dd></div><div class="flex justify-between gap-4 rounded-xl bg-slate-50 px-4 py-3 text-sm"><dt class="text-slate-500">الناتج</dt><dd class="font-black text-slate-900">{{ number_format($summary['processing']['output_weight_kg'], 1) }} كجم</dd></div><div class="flex justify-between gap-4 rounded-xl px-4 py-3 text-sm"><dt class="text-slate-500">الهالك</dt><dd class="font-black text-slate-900">{{ number_format($summary['processing']['waste_weight_kg'], 1) }} كجم</dd></div></dl></div>
        </section>

        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="px-4 py-5 sm:px-6"><h2 class="text-xl font-black text-slate-900">المخزون النهائي</h2></div>
            <div class="overflow-x-auto"><table class="min-w-[760px] w-full text-right text-sm"><thead class="bg-slate-50 text-slate-500"><tr><th class="px-4 py-3 font-bold sm:px-6">المنتج</th><th class="px-4 py-3 font-bold">الكود</th><th class="px-4 py-3 font-bold">النوع</th><th class="px-4 py-3 font-bold">الرصيد</th><th class="px-4 py-3 font-bold">الوحدة</th></tr></thead><tbody class="divide-y divide-slate-100">@forelse ($summary['finished_product_stock'] as $product)<tr class="transition hover:bg-slate-50"><td class="px-4 py-4 font-bold text-slate-900 sm:px-6">{{ $product['name'] }}</td><td class="px-4 py-4 text-slate-600">{{ $product['code'] }}</td><td class="px-4 py-4 text-slate-600">{{ $product['type'] }}</td><td class="px-4 py-4 font-black">{{ number_format($product['quantity'], 3) }}</td><td class="px-4 py-4 text-slate-600">{{ $product['unit'] }}</td></tr>@empty<tr><td colspan="5" class="px-4 py-10 text-center text-slate-500">لا يوجد مخزون منتج نهائي.</td></tr>@endforelse</tbody></table></div>
        </section>

        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="px-4 py-5 sm:px-6"><h2 class="text-xl font-black text-slate-900">المخازن المبردة</h2></div>
            <div class="overflow-x-auto"><table class="min-w-[650px] w-full text-right text-sm"><thead class="bg-slate-50 text-slate-500"><tr><th class="px-4 py-3 font-bold sm:px-6">البراد</th><th class="px-4 py-3 font-bold">الأقفاص</th><th class="px-4 py-3 font-bold">الوزن</th><th class="px-4 py-3 font-bold">عهدة مفتوحة</th></tr></thead><tbody class="divide-y divide-slate-100">@forelse ($summary['cold_stores'] as $store)<tr class="transition hover:bg-slate-50"><td class="px-4 py-4 font-bold text-slate-900 sm:px-6">{{ $store['name'] }}</td><td class="px-4 py-4">{{ number_format($store['current_crates_count']) }}</td><td class="px-4 py-4">{{ number_format($store['current_weight_kg'], 1) }} كجم</td><td class="px-4 py-4 font-black text-slate-900">{{ number_format($store['custody_outstanding_crates']) }}</td></tr>@empty<tr><td colspan="4" class="px-4 py-10 text-center text-slate-500">لا توجد مخازن نشطة.</td></tr>@endforelse</tbody></table></div>
        </section>

        <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6"><h2 class="text-xl font-black text-slate-900">المبيعات والتحصيل</h2><dl class="mt-5 grid gap-3 sm:grid-cols-3"><div class="rounded-2xl bg-slate-50 p-4"><dt class="text-sm text-slate-500">عدد الفواتير</dt><dd class="mt-1 text-2xl font-black text-slate-900">{{ number_format($summary['sales']['count']) }}</dd></div><div class="rounded-2xl bg-slate-50 p-4"><dt class="text-sm text-slate-500">المدفوع</dt><dd class="mt-1 text-2xl font-black text-slate-900">{{ number_format($summary['sales']['paid_amount'], 2) }} ج.م</dd></div><div class="rounded-2xl bg-slate-50 p-4"><dt class="text-sm text-slate-500">المتبقي</dt><dd class="mt-1 text-2xl font-black text-slate-900">{{ number_format($summary['sales']['balance_due'], 2) }} ج.م</dd></div></dl></section>
    </main>
</x-layouts.app>
