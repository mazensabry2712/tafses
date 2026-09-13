<x-layouts.app title="لوحة التحكم">
    <main dir="rtl" class="mx-auto w-full max-w-7xl space-y-6 px-4 py-5 sm:px-6 sm:py-7 lg:px-8 lg:py-8">
        <section class="rounded-3xl bg-gradient-to-l from-slate-950 via-slate-900 to-slate-800 p-5 text-white shadow-soft sm:p-7">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-2xl">
                    <p class="text-sm font-semibold text-green-400">نظام قطاف الرمان والتصنيع</p>
                    <h1 class="mt-2 text-2xl font-black tracking-tight sm:text-3xl lg:text-4xl">لوحة التحكم</h1>
                    <p class="mt-3 text-sm leading-7 text-slate-300 sm:text-base">
                        ملخص العمليات المسجلة بتاريخ {{ \Carbon\Carbon::parse($report['period']['from'])->translatedFormat('d F Y') }}.
                    </p>
                </div>
                <div class="flex items-center gap-3 rounded-2xl border border-white/10 bg-white/5 p-3 sm:min-w-64 sm:p-4">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-green-600 font-bold">{{ mb_substr(auth()->user()->name, 0, 1) }}</div>
                    <div class="min-w-0">
                        <p class="truncate text-sm font-bold">{{ auth()->user()->name }}</p>
                        <p class="mt-1 text-xs text-slate-400">{{ auth()->user()->role }}</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between gap-4"><div><p class="text-sm font-semibold text-slate-500">الشحنات المستلمة</p><p class="mt-3 text-3xl font-black text-slate-900">{{ number_format($report['receiving']['loads_count']) }}</p></div><span class="rounded-xl bg-blue-50 px-3 py-2 text-lg">📥</span></div>
                <p class="mt-3 text-sm text-slate-500">{{ number_format($report['receiving']['crates_count']) }} قفص</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between gap-4"><div><p class="text-sm font-semibold text-slate-500">وزن الاستلام</p><p class="mt-3 text-3xl font-black text-slate-900">{{ number_format($report['receiving']['weight_kg'], 1) }}</p></div><span class="rounded-xl bg-amber-50 px-3 py-2 text-lg">⚖️</span></div>
                <p class="mt-3 text-sm text-slate-500">كجم</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between gap-4"><div><p class="text-sm font-semibold text-slate-500">إنتاج اليوم</p><p class="mt-3 text-3xl font-black text-slate-900">{{ number_format($report['processing']['output_weight_kg'], 1) }}</p></div><span class="rounded-xl bg-green-50 px-3 py-2 text-lg">⚙️</span></div>
                <p class="mt-3 text-sm text-slate-500">كجم منتج</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between gap-4"><div><p class="text-sm font-semibold text-slate-500">مبيعات اليوم</p><p class="mt-3 text-3xl font-black text-slate-900">{{ number_format($report['sales']['total_amount'], 2) }}</p></div><span class="rounded-xl bg-emerald-50 px-3 py-2 text-lg">💰</span></div>
                <p class="mt-3 text-sm text-slate-500">جنيه مصري</p>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-3">
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm xl:col-span-2">
                <div class="flex flex-col gap-3 border-b border-slate-100 p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6">
                    <div><h2 class="text-lg font-black text-slate-900">المخازن المبردة</h2><p class="mt-1 text-sm text-slate-500">الرصيد الحالي في المخازن المفتوحة</p></div>
                    @canPermission('manage_cold_stores')<a href="{{ route('warehouse') }}" class="inline-flex w-fit items-center rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-bold text-white hover:bg-slate-800">إدارة المخازن</a>@endcanPermission
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-right text-sm">
                        <thead class="bg-slate-50 text-slate-500"><tr><th class="whitespace-nowrap px-5 py-3 font-semibold">المخزن</th><th class="whitespace-nowrap px-5 py-3 font-semibold">الأقفاص</th><th class="whitespace-nowrap px-5 py-3 font-semibold">الوزن</th><th class="whitespace-nowrap px-5 py-3 font-semibold">العُهدة</th></tr></thead>
                        <tbody>
                        @forelse ($report['cold_stores'] as $store)
                            <tr class="border-t border-slate-100"><td class="whitespace-nowrap px-5 py-4 font-bold text-slate-800">{{ $store['name'] }}</td><td class="whitespace-nowrap px-5 py-4">{{ number_format($store['current_crates_count']) }}</td><td class="whitespace-nowrap px-5 py-4">{{ number_format($store['current_weight_kg'], 1) }} كجم</td><td class="whitespace-nowrap px-5 py-4">{{ number_format($store['custody_outstanding_crates']) }}</td></tr>
                        @empty
                            <tr><td colspan="4" class="px-5 py-12 text-center text-sm text-slate-500">لا توجد مخازن مبردة نشطة حتى الآن.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <h2 class="text-lg font-black text-slate-900">اختصارات النظام</h2>
                <p class="mt-1 text-sm text-slate-500">الأقسام المتاحة حسب صلاحياتك</p>
                <div class="mt-5 grid gap-2 sm:grid-cols-2 xl:grid-cols-1">
                    @canPermission('receive_loads')<a href="{{ route('receiving.index') }}" class="rounded-xl border border-slate-200 px-4 py-3 text-sm font-bold transition hover:border-blue-200 hover:bg-blue-50">الاستلام والشحنات</a>@endcanPermission
                    @canPermission('manage_cold_stores')<a href="{{ route('warehouse') }}" class="rounded-xl border border-slate-200 px-4 py-3 text-sm font-bold transition hover:border-blue-200 hover:bg-blue-50">المخازن</a>@endcanPermission
                    @canPermission('manage_custody')<a href="{{ route('warehouse.custody') }}" class="rounded-xl border border-slate-200 px-4 py-3 text-sm font-bold transition hover:border-blue-200 hover:bg-blue-50">العُهد</a>@endcanPermission
                    @canPermission('process_pomegranates')<a href="{{ route('processing.index') }}" class="rounded-xl border border-slate-200 px-4 py-3 text-sm font-bold transition hover:border-green-200 hover:bg-green-50">التصنيع</a>@endcanPermission
                    @canPermission('manage_sales')<a href="{{ route('sales.index') }}" class="rounded-xl border border-slate-200 px-4 py-3 text-sm font-bold transition hover:border-emerald-200 hover:bg-emerald-50">المبيعات والعملاء</a>@endcanPermission
                    @canPermission('manage_suppliers')<a href="{{ route('suppliers.index') }}" class="rounded-xl border border-slate-200 px-4 py-3 text-sm font-bold transition hover:border-amber-200 hover:bg-amber-50">الموردون والمشتريات</a>@endcanPermission
                    @canPermission('view_reports')<a href="{{ route('reports.index') }}" class="rounded-xl border border-slate-200 px-4 py-3 text-sm font-bold transition hover:border-violet-200 hover:bg-violet-50">التقارير</a>@endcanPermission
                    @canPermission('manage_users')<a href="{{ route('management') }}" class="rounded-xl border border-slate-200 px-4 py-3 text-sm font-bold transition hover:border-slate-300 hover:bg-slate-50">إدارة المستخدمين</a>@endcanPermission
                </div>
            </div>
        </section>

        <section class="grid gap-6 md:grid-cols-3">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6"><h2 class="font-black text-slate-900">التصنيع</h2><dl class="mt-5 space-y-4 text-sm"><div class="flex items-center justify-between"><dt class="text-slate-500">دفعات التصنيع</dt><dd class="font-black">{{ number_format($report['processing']['batches_count']) }}</dd></div><div class="flex items-center justify-between"><dt class="text-slate-500">الداخل</dt><dd class="font-bold">{{ number_format($report['processing']['input_weight_kg'], 1) }} كجم</dd></div><div class="flex items-center justify-between"><dt class="text-slate-500">الناتج</dt><dd class="font-bold text-green-700">{{ number_format($report['processing']['output_weight_kg'], 1) }} كجم</dd></div><div class="flex items-center justify-between"><dt class="text-slate-500">الهالك</dt><dd class="font-bold text-red-600">{{ number_format($report['processing']['waste_weight_kg'], 1) }} كجم</dd></div></dl></div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6"><h2 class="font-black text-slate-900">الحسابات والمبيعات</h2><dl class="mt-5 space-y-4 text-sm"><div class="flex items-center justify-between"><dt class="text-slate-500">عدد الفواتير</dt><dd class="font-black">{{ number_format($report['sales']['count']) }}</dd></div><div class="flex items-center justify-between"><dt class="text-slate-500">إجمالي المبيعات</dt><dd class="font-bold">{{ number_format($report['sales']['total_amount'], 2) }} ج.م</dd></div><div class="flex items-center justify-between"><dt class="text-slate-500">المدفوع</dt><dd class="font-bold text-green-700">{{ number_format($report['sales']['paid_amount'], 2) }} ج.م</dd></div><div class="flex items-center justify-between"><dt class="text-slate-500">المتبقي</dt><dd class="font-bold text-red-600">{{ number_format($report['sales']['balance_due'], 2) }} ج.م</dd></div></dl></div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6"><h2 class="font-black text-slate-900">المشتريات والموردون</h2><dl class="mt-5 space-y-4 text-sm"><div class="flex items-center justify-between"><dt class="text-slate-500">المشتريات</dt><dd class="font-black">{{ number_format($report['purchases']['count']) }}</dd></div><div class="flex items-center justify-between"><dt class="text-slate-500">إجمالي المشتريات</dt><dd class="font-bold">{{ number_format($report['purchases']['total_amount'], 2) }} ج.م</dd></div><div class="flex items-center justify-between"><dt class="text-slate-500">المدفوع للموردين</dt><dd class="font-bold text-green-700">{{ number_format($report['purchases']['paid_amount'], 2) }} ج.م</dd></div><div class="flex items-center justify-between"><dt class="text-slate-500">مستحق للموردين</dt><dd class="font-bold text-red-600">{{ number_format($report['purchases']['balance_due'], 2) }} ج.م</dd></div></dl></div>
        </section>
    </main>
</x-layouts.app>
