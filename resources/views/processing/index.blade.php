<x-layouts.app title="التصنيع">
    <main dir="rtl" class="mx-auto max-w-7xl space-y-6 px-6 py-8">
        <section>
            <p class="text-sm font-medium text-gray-500">تشغيل وتصنيع الرمان</p>
            <h1 class="mt-1 text-3xl font-bold">التصنيع</h1>
            <p class="mt-2 text-sm text-gray-600">تحويل الرمان المتاح إلى حبوب أو عصير مع تسجيل الناتج والهالك.</p>
        </section>

        <section class="grid gap-4 md:grid-cols-2">
            <div class="rounded-2xl border bg-white p-5 shadow-sm">
                <p class="text-sm text-gray-500">حمولات متاحة للتصنيع</p>
                <p class="mt-2 text-3xl font-bold">{{ $loads->count() }}</p>
                <p class="mt-1 text-sm text-gray-500">حمولة بها أقفاص ووزن متاحان</p>
            </div>
            <div class="rounded-2xl border bg-white p-5 shadow-sm">
                <p class="text-sm text-gray-500">منتجات مصنعة</p>
                <p class="mt-2 text-3xl font-bold">{{ $products->count() }}</p>
                <p class="mt-1 text-sm text-gray-500">منتجات نشطة في المخزون</p>
            </div>
        </section>

        <section class="rounded-2xl border bg-white p-6 shadow-sm">
            <div class="mb-5">
                <h2 class="text-xl font-bold">تسجيل دفعة تصنيع</h2>
                <p class="mt-1 text-sm text-gray-500">اختر الحمولة وحدد نوع التشغيل والكميات الفعلية.</p>
            </div>

            @forelse ($loads as $load)
                <form method="POST" action="{{ route('processing.store', $load) }}" class="mb-4 grid gap-3 rounded-xl border p-4 lg:grid-cols-7">
                    @csrf
                    <div class="lg:col-span-2">
                        <div class="font-semibold">{{ $load->load_number }}</div>
                        <div class="mt-1 text-xs text-gray-500">المتاح: {{ $load->available_crates_count }} قفص — {{ number_format((float) $load->available_weight_kg, 3) }} كجم</div>
                    </div>
                    <select name="process_type" required class="rounded-lg border-gray-300">
                        <option value="peeling">تقشير / حبوب</option>
                        <option value="juice">عصير</option>
                    </select>
                    <input name="input_crates_count" type="number" min="1" max="{{ $load->available_crates_count }}" required placeholder="أقفاص الداخل" class="rounded-lg border-gray-300">
                    <input name="input_weight_kg" type="number" step="0.001" min="0.001" max="{{ $load->available_weight_kg }}" required placeholder="وزن الداخل كجم" class="rounded-lg border-gray-300">
                    <input name="output_weight_kg" type="number" step="0.001" min="0" required placeholder="الناتج كجم" class="rounded-lg border-gray-300">
                    <input name="waste_weight_kg" type="number" step="0.001" min="0" placeholder="الهالك كجم" class="rounded-lg border-gray-300">
                    <div class="flex gap-2 lg:col-span-7">
                        <input name="notes" type="text" placeholder="ملاحظات" class="flex-1 rounded-lg border-gray-300">
                        <button class="rounded-lg bg-gray-900 px-5 py-2 font-semibold text-white hover:bg-gray-700">تسجيل التصنيع</button>
                    </div>
                </form>
            @empty
                <div class="rounded-xl bg-gray-50 px-5 py-8 text-center text-sm text-gray-500">لا توجد حمولات متاحة للتصنيع حاليًا.</div>
            @endforelse
        </section>

        <section class="rounded-2xl border bg-white p-6 shadow-sm">
            <h2 class="text-xl font-bold">مخزون المنتجات النهائية</h2>
            <div class="mt-5 overflow-x-auto">
                <table class="min-w-full text-right text-sm">
                    <thead class="border-b text-gray-500">
                        <tr>
                            <th class="px-3 py-3 font-medium">المنتج</th>
                            <th class="px-3 py-3 font-medium">النوع</th>
                            <th class="px-3 py-3 font-medium">الكود</th>
                            <th class="px-3 py-3 font-medium">المخزون</th>
                            <th class="px-3 py-3 font-medium">الوحدة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($products as $product)
                            <tr class="border-b last:border-0">
                                <td class="px-3 py-3 font-semibold">{{ $product->name }}</td>
                                <td class="px-3 py-3">{{ $product->type === 'peeling' ? 'حبوب' : 'عصير' }}</td>
                                <td class="px-3 py-3">{{ $product->code }}</td>
                                <td class="px-3 py-3">{{ number_format((float) ($product->stock?->quantity ?? 0), 3) }}</td>
                                <td class="px-3 py-3">{{ $product->unit }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-3 py-8 text-center text-gray-500">لا يوجد مخزون منتجات نهائية حتى الآن.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        @if ($errors->any())
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                <ul class="list-disc space-y-1 pr-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </main>
</x-layouts.app>
