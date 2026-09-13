<x-layouts.app title="الحسابات">
    <main dir="rtl" class="mx-auto max-w-7xl space-y-6 px-6 py-8">
        <section>
            <h1 class="text-2xl font-bold">الحسابات</h1>
            <p class="mt-1 text-sm text-gray-600">أرصدة العملاء والموردين ومتابعة المستحقات.</p>
        </section>

        <section class="grid gap-6 lg:grid-cols-2">
            <div class="overflow-hidden rounded-2xl border bg-white shadow-sm">
                <div class="border-b px-5 py-4"><h2 class="font-semibold">حسابات العملاء</h2></div>
                <div class="overflow-x-auto"><table class="min-w-full text-right text-sm"><thead class="bg-gray-50 text-gray-500"><tr><th class="px-4 py-3">العميل</th><th class="px-4 py-3">المبيعات</th><th class="px-4 py-3">المدفوع</th><th class="px-4 py-3">المتبقي</th></tr></thead><tbody>
                @forelse ($customers as $row)
                    <tr class="border-t"><td class="px-4 py-3 font-semibold"><a href="{{ route('sales.customer', $row['customer']) }}" class="hover:underline">{{ $row['customer']->name }}</a></td><td class="px-4 py-3">{{ number_format($row['balance']['sales_total'], 2) }} ج.م</td><td class="px-4 py-3">{{ number_format($row['balance']['paid_total'], 2) }} ج.م</td><td class="px-4 py-3 font-semibold">{{ number_format($row['balance']['balance_due'], 2) }} ج.م</td></tr>
                @empty <tr><td colspan="4" class="px-4 py-8 text-center text-gray-500">لا يوجد عملاء نشطون.</td></tr>@endforelse
                </tbody></table></div>
            </div>

            <div class="overflow-hidden rounded-2xl border bg-white shadow-sm">
                <div class="border-b px-5 py-4"><h2 class="font-semibold">حسابات الموردين</h2></div>
                <div class="overflow-x-auto"><table class="min-w-full text-right text-sm"><thead class="bg-gray-50 text-gray-500"><tr><th class="px-4 py-3">المورد</th><th class="px-4 py-3">المشتريات</th><th class="px-4 py-3">المدفوع</th><th class="px-4 py-3">المتبقي</th></tr></thead><tbody>
                @forelse ($suppliers as $row)
                    <tr class="border-t"><td class="px-4 py-3 font-semibold"><a href="{{ route('suppliers.show', $row['supplier']) }}" class="hover:underline">{{ $row['supplier']->name }}</a></td><td class="px-4 py-3">{{ number_format($row['balance']['purchase_total'], 2) }} ج.م</td><td class="px-4 py-3">{{ number_format($row['balance']['paid_total'], 2) }} ج.م</td><td class="px-4 py-3 font-semibold">{{ number_format($row['balance']['balance_due'], 2) }} ج.م</td></tr>
                @empty <tr><td colspan="4" class="px-4 py-8 text-center text-gray-500">لا يوجد موردون.</td></tr>@endforelse
                </tbody></table></div>
            </div>
        </section>
    </main>
</x-layouts.app>
