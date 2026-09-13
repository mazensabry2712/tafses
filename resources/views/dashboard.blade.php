<x-layouts.app title="لوحة التحكم">
    <main dir="rtl" class="mx-auto max-w-7xl space-y-8 px-6 py-8">
        <section class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">نظام قطاف الرمان والتصنيع</p>
                <h1 class="mt-1 text-3xl font-bold tracking-tight">لوحة التحكم</h1>
                <p class="mt-2 text-sm text-gray-600">
                    ملخص العمليات المسجلة اليوم: {{ \Carbon\Carbon::parse($report['period']['from'])->translatedFormat('d F Y') }}
                </p>
            </div>
            <div class="rounded-xl border bg-white px-4 py-3 text-sm shadow-sm">
                <span class="text-gray-500">المستخدم</span>
                <span class="mr-2 font-semibold">{{ auth()->user()->name }}</span>
                <span class="mr-2 rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold uppercase text-gray-700">
                    {{ auth()->user()->role }}
                </span>
            </div>
        </section>

        <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-2xl border bg-white p-5 shadow-sm"><p class="text-sm text-gray-500">الشحنات المستلمة</p><p class="mt-2 text-3xl font-bold">{{ number_format($report['receiving']['loads_count']) }}</p><p class="mt-1 text-sm text-gray-500">{{ number_format($report['receiving']['crates_count']) }} قفص</p></div>
            <div class="rounded-2xl border bg-white p-5 shadow-sm"><p class="text-sm text-gray-500">وزن الاستلام</p><p class="mt-2 text-3xl font-bold">{{ number_format($report['receiving']['weight_kg'], 1) }}</p><p class="mt-1 text-sm text-gray-500">كجم</p></div>
            <div class="rounded-2xl border bg-white p-5 shadow-sm"><p class="text-sm text-gray-500">إنتاج اليوم</p><p class="mt-2 text-3xl font-bold">{{ number_format($report['processing']['output_weight_kg'], 1) }}</p><p class="mt-1 text-sm text-gray-500">كجم منتج</p></div>
            <div class="rounded-2xl border bg-white p-5 shadow-sm"><p class="text-sm text-gray-500">مبيعات اليوم</p><p class="mt-2 text-3xl font-bold">{{ number_format($report['sales']['total_amount'], 2) }}</p><p class="mt-1 text-sm text-gray-500">جنيه</p></div>
        </section>

        <section class="grid gap-6 lg:grid-cols-3">
            <div class="rounded-2xl border bg-white p-6 shadow-sm lg:col-span-2">
                <div class="flex items-center justify-between gap-4"><div><h2 class="text-lg font-bold">المخازن المبردة</h2><p class="mt-1 text-sm text-gray-500">الرصيد الحالي في المخازن المفتوحة</p></div>@canPermission('manage_cold_stores')<a href="{{ route('warehouse') }}" class="rounded-lg border px-3 py-2 text-sm font-semibold hover:bg-gray-50">إدارة المخازن</a>@endcanPermission</div>
                <div class="mt-6 overflow-x-auto"><table class="min-w-full text-right text-sm"><thead><tr class="border-b text-gray-500"><th class="px-3 py-3 font-medium">المخزن</th><th class="px-3 py-3 font-medium">الأقفاص</th><th class="px-3 py-3 font-medium">الوزن</th><th class="px-3 py-3 font-medium">العهدة</th></tr></thead><tbody>@forelse ($report['cold_stores'] as $store)<tr class="border-b last:border-0"><td class="px-3 py-3 font-semibold">{{ $store['name'] }}</td><td class="px-3 py-3">{{ number_format($store['current_crates_count']) }}</td><td class="px-3 py-3">{{ number_format($store['current_weight_kg'], 1) }} كجم</td><td class="px-3 py-3">{{ number_format($store['custody_outstanding_crates']) }}</td></tr>@empty<tr><td colspan="4" class="px-3 py-8 text-center text-gray-500">لا توجد مخازن مبردة نشطة حتى الآن.</td></tr>@endforelse</tbody></table></div>
            </div>
            <div class="rounded-2xl border bg-white p-6 shadow-sm"><h2 class="text-lg font-bold">اختصارات النظام</h2><p class="mt-1 text-sm text-gray-500">الأقسام المسموح بها حسب صلاحياتك</p><div class="mt-5 space-y-2">
                @canPermission('receive_loads')<a href="{{ route('receiving.index') }}" class="block rounded-xl border px-4 py-3 font-medium hover:bg-gray-50">الاستلام والشحنات</a>@endcanPermission
                @canPermission('manage_cold_stores')<a href="{{ route('warehouse') }}" class="block rounded-xl border px-4 py-3 font-medium hover:bg-gray-50">المخازن</a>@endcanPermission
                @canPermission('manage_custody')<a href="{{ route('warehouse.custody') }}" class="block rounded-xl border px-4 py-3 font-medium hover:bg-gray-50">العُهد</a>@endcanPermission
                @canPermission('process_pomegranates')<a href="{{ route('processing.index') }}" class="block rounded-xl border px-4 py-3 font-medium hover:bg-gray-50">التصنيع</a>@endcanPermission
                @canPermission('manage_sales')<a href="{{ route('sales.index') }}" class="block rounded-xl border px-4 py-3 font-medium hover:bg-gray-50">المبيعات والعملاء</a>@endcanPermission
                @canPermission('manage_suppliers')<a href="{{ route('suppliers.index') }}" class="block rounded-xl border px-4 py-3 font-medium hover:bg-gray-50">الموردون والمشتريات</a>@endcanPermission
                @canPermission('view_reports')<a href="{{ route('reports.index') }}" class="block rounded-xl border px-4 py-3 font-medium hover:bg-gray-50">التقارير</a>@endcanPermission
                @canPermission('view_audit')<a href="{{ route('audit.index') }}" class="block rounded-xl border px-4 py-3 font-medium hover:bg-gray-50">سجل التدقيق</a>@endcanPermission
                @canPermission('manage_users')<a href="{{ route('management') }}" class="block rounded-xl border px-4 py-3 font-medium hover:bg-gray-50">إدارة المستخدمين</a>@endcanPermission
            </div></div>
        </section>

        <section class="grid gap-6 md:grid-cols-3">
            <div class="rounded-2xl border bg-white p-6 shadow-sm"><h2 class="text-lg font-bold">التصنيع</h2><dl class="mt-4 space-y-3 text-sm"><div class="flex justify-between"><dt class="text-gray-500">دفعات التصنيع</dt><dd class="font-semibold">{{ number_format($report['processing']['batches_count']) }}</dd></div><div class="flex justify-between"><dt class="text-gray-500">الداخل</dt><dd class="font-semibold">{{ number_format($report['processing']['input_weight_kg'], 1) }} كجم</dd></div><div class="flex justify-between"><dt class="text-gray-500">الناتج</dt><dd class="font-semibold">{{ number_format($report['processing']['output_weight_kg'], 1) }} كجم</dd></div><div class="flex justify-between"><dt class="text-gray-500">الهالك</dt><dd class="font-semibold">{{ number_format($report['processing']['waste_weight_kg'], 1) }} كجم</dd></div></dl></div>
            <div class="rounded-2xl border bg-white p-6 shadow-sm"><h2 class="text-lg font-bold">الحسابات والمبيعات</h2><dl class="mt-4 space-y-3 text-sm"><div class="flex justify-between"><dt class="text-gray-500">عدد الفواتير</dt><dd class="font-semibold">{{ number_format($report['sales']['count']) }}</dd></div><div class="flex justify-between"><dt class="text-gray-500">إجمالي المبيعات</dt><dd class="font-semibold">{{ number_format($report['sales']['total_amount'], 2) }} ج.م</dd></div><div class="flex justify-between"><dt class="text-gray-500">المدفوع</dt><dd class="font-semibold">{{ number_format($report['sales']['paid_amount'], 2) }} ج.م</dd></div><div class="flex justify-between"><dt class="text-gray-500">المتبقي</dt><dd class="font-semibold">{{ number_format($report['sales']['balance_due'], 2) }} ج.م</dd></div></dl></div>
            <div class="rounded-2xl border bg-white p-6 shadow-sm"><h2 class="text-lg font-bold">المشتريات والموردون</h2><dl class="mt-4 space-y-3 text-sm"><div class="flex justify-between"><dt class="text-gray-500">المشتريات</dt><dd class="font-semibold">{{ number_format($report['purchases']['count']) }}</dd></div><div class="flex justify-between"><dt class="text-gray-500">إجمالي المشتريات</dt><dd class="font-semibold">{{ number_format($report['purchases']['total_amount'], 2) }} ج.م</dd></div><div class="flex justify-between"><dt class="text-gray-500">المدفوع للموردين</dt><dd class="font-semibold">{{ number_format($report['purchases']['paid_amount'], 2) }} ج.م</dd></div><div class="flex justify-between"><dt class="text-gray-500">مستحق للموردين</dt><dd class="font-semibold">{{ number_format($report['purchases']['balance_due'], 2) }} ج.م</dd></div></dl></div>
        </section>
    </main>
</x-layouts.app>
