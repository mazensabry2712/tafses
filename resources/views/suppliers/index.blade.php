<x-layouts.app title="الموردون والمشتريات">
    <main dir="rtl" class="mx-auto max-w-7xl space-y-6 px-6 py-8">
        <div>
            <h1 class="text-2xl font-bold">الموردون والمشتريات</h1>
            <p class="mt-1 text-sm text-gray-600">إدارة الموردين، شراء حمولات الرمان، ومتابعة المستحقات.</p>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <section class="rounded-2xl border bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold">إضافة مورد</h2>
                <form method="POST" action="{{ route('suppliers.store') }}" class="mt-4 space-y-3">
                    @csrf
                    <input name="name" required placeholder="اسم المورد" class="w-full rounded-lg border-gray-300">
                    <input name="phone" placeholder="الهاتف" class="w-full rounded-lg border-gray-300">
                    <input name="address" placeholder="العنوان" class="w-full rounded-lg border-gray-300">
                    <textarea name="notes" placeholder="ملاحظات" class="w-full rounded-lg border-gray-300"></textarea>
                    <button class="w-full rounded-lg bg-gray-900 px-4 py-2 font-medium text-white">حفظ المورد</button>
                </form>
            </section>

            <section class="rounded-2xl border bg-white p-6 shadow-sm lg:col-span-2">
                <h2 class="text-lg font-semibold">رصيد الموردين</h2>
                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full text-right text-sm">
                        <thead class="border-b text-gray-500"><tr><th class="px-3 py-3">المورد</th><th class="px-3 py-3">إجمالي المشتريات</th><th class="px-3 py-3">المدفوع</th><th class="px-3 py-3">المستحق</th></tr></thead>
                        <tbody>
                        @forelse ($suppliers as $row)
                            <tr class="border-b last:border-0">
                                <td class="px-3 py-3"><a class="font-semibold hover:underline" href="{{ route('suppliers.show', $row['supplier']) }}">{{ $row['supplier']->name }}</a></td>
                                <td class="px-3 py-3">{{ number_format($row['balance']['purchase_total'], 2) }} ج.م</td>
                                <td class="px-3 py-3">{{ number_format($row['balance']['paid_total'], 2) }} ج.م</td>
                                <td class="px-3 py-3 font-semibold">{{ number_format($row['balance']['balance_due'], 2) }} ج.م</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-3 py-6 text-center text-gray-500">لا يوجد موردون حتى الآن.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <section class="rounded-2xl border bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold">تسجيل شراء حمولة</h2>
            <p class="mt-1 text-sm text-gray-500">لا تظهر إلا الحمولات التي لها مورد ولم يتم تسجيل شراء لها.</p>
            <div class="mt-5 space-y-4">
                @forelse ($loads as $load)
                    <form method="POST" action="{{ route('suppliers.purchases.store', $load->supplier) }}" class="grid gap-3 rounded-xl border p-4 md:grid-cols-6">
                        @csrf
                        <input type="hidden" name="pomegranate_load_id" value="{{ $load->id }}">
                        <div class="md:col-span-2"><div class="font-semibold">{{ $load->load_number }}</div><div class="text-xs text-gray-500">{{ $load->supplier->name }} — {{ number_format($load->loaded_crates_count) }} قفص / {{ number_format($load->loaded_weight_kg, 1) }} كجم</div></div>
                        <select name="pricing_unit" class="rounded-lg border-gray-300" required><option value="kg">بالكيلو</option><option value="crate">بالقفص</option><option value="fixed">مبلغ ثابت</option></select>
                        <input name="unit_price" type="number" step="0.001" min="0.001" required placeholder="السعر" class="rounded-lg border-gray-300">
                        <input name="quantity" type="number" step="0.001" min="0.001" placeholder="الكمية (اختياري)" class="rounded-lg border-gray-300">
                        <div class="flex gap-2"><input name="initial_paid" type="number" step="0.001" min="0" placeholder="دفعة أولى" class="w-full rounded-lg border-gray-300"><button class="rounded-lg bg-gray-900 px-4 py-2 font-medium text-white">حفظ</button></div>
                    </form>
                @empty
                    <div class="rounded-xl bg-gray-50 px-4 py-6 text-center text-sm text-gray-500">لا توجد حمولات جاهزة لتسجيل شراء.</div>
                @endforelse
            </div>
        </section>

        @if ($errors->any())
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800"><ul class="list-disc pr-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif
    </main>
</x-layouts.app>
