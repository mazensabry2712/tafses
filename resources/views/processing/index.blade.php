<x-layouts.app title="التصنيع">
    <main dir="rtl" class="mx-auto max-w-7xl space-y-6 px-4 py-5 sm:px-6 sm:py-7 lg:px-8">
        <section class="overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-950 via-emerald-900 to-slate-900 p-5 text-white shadow-xl sm:p-7">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <div class="inline-flex rounded-full bg-white/10 px-3 py-1 text-xs font-semibold text-emerald-100 ring-1 ring-white/10">التشغيل / التصنيع</div>
                    <h1 class="mt-3 text-2xl font-black tracking-tight sm:text-3xl">التصنيع</h1>
                    <p class="mt-2 max-w-3xl text-sm leading-6 text-emerald-50/80 sm:text-base">تحويل الرمان المتاح في البرادات إلى حبوب أو عصير مع تسجيل الناتج والهالك.</p>
                </div>
            </div>
        </section>

        <section class="grid gap-4 sm:grid-cols-2">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-sm font-bold text-slate-500">حمولات متاحة للتصنيع</p>
                    <span class="rounded-xl bg-emerald-50 px-3 py-2 text-xs font-bold text-emerald-700">متاح الآن</span>
                </div>
                <p class="mt-3 text-3xl font-black text-slate-900">{{ $loads->count() }}</p>
                <p class="mt-1 text-sm text-slate-500">حمولات بها مخزون متاح</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-sm font-bold text-slate-500">منتجات مصنعة</p>
                    <span class="rounded-xl bg-slate-100 px-3 py-2 text-xs font-bold text-slate-600">المخزون</span>
                </div>
                <p class="mt-3 text-3xl font-black text-slate-900">{{ $products->count() }}</p>
                <p class="mt-1 text-sm text-slate-500">منتجات نشطة في المخزون</p>
            </div>
        </section>

        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-4 py-5 sm:px-6">
                <h2 class="text-xl font-black text-slate-900">تسجيل دفعة تصنيع</h2>
                <p class="mt-1 text-sm leading-6 text-slate-500">اختر البراد الذي خرج منه الرمان، ثم سجل الكميات الفعلية.</p>
            </div>

            <div class="p-4 sm:p-6">
                @forelse ($loads as $load)
                    <form method="POST" action="{{ route('processing.store', $load) }}" class="mb-5 rounded-2xl border border-slate-200 bg-slate-50/70 p-4 last:mb-0 sm:p-5">
                        @csrf
                        <div class="mb-4 flex flex-col gap-3 border-b border-slate-200 pb-4 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <div class="text-base font-black text-slate-900">{{ $load->load_number }}</div>
                                <div class="mt-1 text-xs leading-5 text-slate-500">إجمالي المتاح للحمولة: {{ $load->available_crates_count }} قفص — {{ number_format((float) $load->available_weight_kg, 3) }} كجم</div>
                            </div>
                            <span class="rounded-xl bg-white px-3 py-2 text-xs font-bold text-slate-600 ring-1 ring-slate-200">سجل دفعة جديدة</span>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                            <div class="sm:col-span-2 lg:col-span-2">
                                <label class="block text-sm font-bold text-slate-700">البراد</label>
                                <select name="cold_store_id" required class="mt-2 h-12 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10">
                                    <option value="">اختر البراد</option>
                                    @foreach ($load->coldStoreStocks->filter(fn ($stock) => $stock->coldStore && $stock->crates_count > 0 && (float) $stock->weight_kg > 0) as $coldStoreStock)
                                        <option value="{{ $coldStoreStock->coldStore->id }}">{{ $coldStoreStock->coldStore->name }} — {{ $coldStoreStock->crates_count }} قفص / {{ number_format((float) $coldStoreStock->weight_kg, 3) }} كجم</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700">نوع التصنيع</label>
                                <select name="process_type" required class="mt-2 h-12 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10">
                                    <option value="peeling">تقشير / حبوب</option>
                                    <option value="juice">عصير</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700">أقفاص الداخل</label>
                                <input name="input_crates_count" type="number" min="1" required placeholder="عدد الأقفاص" inputmode="numeric" class="mt-2 h-12 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700">وزن الداخل</label>
                                <input name="input_weight_kg" type="number" step="0.001" min="0.001" required placeholder="كجم" inputmode="decimal" class="mt-2 h-12 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700">الناتج</label>
                                <input name="output_weight_kg" type="number" step="0.001" min="0" required placeholder="كجم" inputmode="decimal" class="mt-2 h-12 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700">الهالك</label>
                                <input name="waste_weight_kg" type="number" step="0.001" min="0" placeholder="كجم" inputmode="decimal" class="mt-2 h-12 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10">
                            </div>
                            <div class="sm:col-span-2 lg:col-span-2">
                                <label class="block text-sm font-bold text-slate-700">ملاحظات</label>
                                <input name="notes" type="text" placeholder="ملاحظات الدفعة (اختياري)" class="mt-2 h-12 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm outline-none transition placeholder:text-slate-400 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10">
                            </div>
                        </div>

                        <div class="mt-5 flex flex-col gap-3 border-t border-slate-200 pt-4 sm:flex-row sm:justify-end">
                            <button class="min-h-12 w-full rounded-xl bg-emerald-700 px-5 py-3 text-sm font-black text-white shadow-sm transition hover:bg-emerald-800 focus:outline-none focus:ring-4 focus:ring-emerald-500/20 sm:w-auto">تسجيل التصنيع</button>
                        </div>
                    </form>
                @empty
                    <div class="rounded-2xl bg-slate-50 px-5 py-10 text-center text-sm text-slate-500">لا توجد حمولات متاحة للتصنيع حاليًا.</div>
                @endforelse
            </div>
        </section>

        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="px-4 py-5 sm:px-6">
                <h2 class="text-xl font-black text-slate-900">مخزون المنتجات النهائية</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-[700px] w-full text-right text-sm">
                    <thead class="bg-slate-50 text-slate-500">
                        <tr>
                            <th class="px-4 py-3 font-bold sm:px-6">المنتج</th>
                            <th class="px-4 py-3 font-bold">النوع</th>
                            <th class="px-4 py-3 font-bold">الكود</th>
                            <th class="px-4 py-3 font-bold">المخزون</th>
                            <th class="px-4 py-3 font-bold">الوحدة</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($products as $product)
                            <tr class="transition hover:bg-slate-50">
                                <td class="px-4 py-4 font-bold text-slate-900 sm:px-6">{{ $product->name }}</td>
                                <td class="px-4 py-4 text-slate-600">{{ $product->type === 'peeling' ? 'حبوب' : 'عصير' }}</td>
                                <td class="px-4 py-4 text-slate-600">{{ $product->code }}</td>
                                <td class="px-4 py-4 font-bold text-slate-900">{{ number_format((float) ($product->stock?->quantity ?? 0), 3) }}</td>
                                <td class="px-4 py-4 text-slate-600">{{ $product->unit }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-10 text-center text-slate-500">لا يوجد مخزون منتجات نهائية حتى الآن.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        @if ($errors->any())
            <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-4 text-sm text-red-800">
                <ul class="list-disc space-y-1 pr-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </main>
</x-layouts.app>
