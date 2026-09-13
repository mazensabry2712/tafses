<x-layouts.app title="التفصيص والتصنيع">
    <main dir="rtl" class="mx-auto w-full max-w-7xl space-y-6 px-4 py-5 sm:px-6 sm:py-7 lg:px-8 lg:py-8">
        <section class="overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-950 via-emerald-900 to-slate-900 p-5 text-white shadow-xl sm:p-7">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div class="max-w-3xl">
                    <div class="inline-flex rounded-full bg-white/10 px-3 py-1 text-xs font-bold text-emerald-100 ring-1 ring-white/10">قسم التفصيص</div>
                    <h1 class="mt-3 text-2xl font-black tracking-tight sm:text-3xl lg:text-4xl">كل ما يخص التفصيص في صفحة واحدة</h1>
                    <p class="mt-3 text-sm leading-7 text-emerald-50/80 sm:text-base">
                        متابعة الرمان المتاح، اختيار البراد والحمولة، تسجيل الداخل والناتج والهالك، ثم مراجعة مخزون الحبوب وسجل دفعات التفصيص من نفس الشاشة.
                    </p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-slate-200">
                    <span class="font-bold text-white">سجل العمليات:</span> آخر {{ $recentBatches->count() }} دفعة
                </div>
            </div>
        </section>

        @if ($errors->any())
            <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-4 text-sm text-red-800">
                <p class="font-black">راجع البيانات التالية:</p>
                <ul class="mt-2 list-disc space-y-1 pr-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-bold text-slate-500">حمولات متاحة</p>
                <p class="mt-2 text-3xl font-black text-slate-900">{{ number_format($loads->count()) }}</p>
                <p class="mt-2 text-xs text-slate-500">حمولة بها رصيد خام</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-bold text-slate-500">وزن الداخل</p>
                <p class="mt-2 text-3xl font-black text-slate-900">{{ number_format($processingSummary['input_weight_kg'], 1) }}</p>
                <p class="mt-2 text-xs text-slate-500">كجم — آخر 30 دفعة</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-bold text-slate-500">ناتج التفصيص</p>
                <p class="mt-2 text-3xl font-black text-green-700">{{ number_format($processingSummary['output_weight_kg'], 1) }}</p>
                <p class="mt-2 text-xs text-slate-500">كجم منتج</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-bold text-slate-500">الهالك</p>
                <p class="mt-2 text-3xl font-black text-red-600">{{ number_format($processingSummary['waste_weight_kg'], 1) }}</p>
                <p class="mt-2 text-xs text-slate-500">كجم</p>
            </div>
        </section>

        <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <div class="flex flex-col gap-2 border-b border-slate-100 pb-5 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h2 class="text-xl font-black text-slate-900">تسجيل دفعة تصنيع / تشغيل التفصيص</h2>
                    <p class="mt-1 text-sm leading-6 text-slate-500">سجل العملية كاملة من البراد إلى الناتج النهائي في نفس النموذج.</p>
                </div>
                <span class="rounded-full bg-green-50 px-3 py-1.5 text-xs font-black text-green-700">المخزون يتحدث تلقائيًا بعد التسجيل</span>
            </div>

            <div class="mt-5 space-y-4">
                @forelse ($loads as $load)
                    <form method="POST" action="{{ route('processing.store', $load) }}" class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4 sm:p-5">
                        @csrf
                        <div class="grid gap-4 xl:grid-cols-12">
                            <div class="xl:col-span-3">
                                <p class="text-xs font-bold text-slate-500">الحمولة</p>
                                <p class="mt-1 text-base font-black text-slate-900">{{ $load->load_number }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ $load->supplier?->name ?? 'بدون مورد' }}</p>
                                <div class="mt-3 grid grid-cols-2 gap-2">
                                    <div class="rounded-xl bg-white p-3 ring-1 ring-slate-100"><p class="text-[11px] text-slate-500">الأقفاص المتاحة</p><p class="mt-1 text-lg font-black">{{ $load->available_crates_count }}</p></div>
                                    <div class="rounded-xl bg-white p-3 ring-1 ring-slate-100"><p class="text-[11px] text-slate-500">الوزن المتاح</p><p class="mt-1 text-lg font-black">{{ number_format((float) $load->available_weight_kg, 1) }}</p><p class="text-[11px] text-slate-500">كجم</p></div>
                                </div>
                            </div>

                            <div class="xl:col-span-3">
                                <label class="block text-sm font-bold text-slate-700">البراد *</label>
                                <select name="cold_store_id" required class="mt-2 h-12 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10">
                                    <option value="">اختر البراد</option>
                                    @foreach ($load->coldStoreStocks->filter(fn ($stock) => $stock->coldStore && $stock->coldStore->is_active && $stock->crates_count > 0 && (float) $stock->weight_kg > 0) as $coldStoreStock)
                                        <option value="{{ $coldStoreStock->cold_store_id }}">{{ $coldStoreStock->coldStore->name }} — {{ $coldStoreStock->crates_count }} قفص / {{ number_format((float) $coldStoreStock->weight_kg, 1) }} كجم</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="xl:col-span-2">
                                <label class="block text-sm font-bold text-slate-700">نوع العملية *</label>
                                <select name="process_type" required class="mt-2 h-12 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10">
                                    <option value="peeling">تفصيص</option>
                                    <option value="juice">عصير</option>
                                </select>
                            </div>

                            <div class="xl:col-span-2">
                                <label class="block text-sm font-bold text-slate-700">عدد الأقفاص *</label>
                                <input type="number" name="input_crates_count" min="1" max="{{ $load->available_crates_count }}" required class="mt-2 h-12 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10">
                            </div>

                            <div class="xl:col-span-2">
                                <label class="block text-sm font-bold text-slate-700">وزن الداخل (كجم) *</label>
                                <input type="number" name="input_weight_kg" min="0.01" max="{{ $load->available_weight_kg }}" step="0.01" required class="mt-2 h-12 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10">
                            </div>

                            <div class="xl:col-span-4">
                                <label class="block text-sm font-bold text-slate-700">وزن الناتج (كجم) *</label>
                                <input type="number" name="output_weight_kg" min="0" step="0.01" required class="mt-2 h-12 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10">
                            </div>

                            <div class="xl:col-span-4">
                                <label class="block text-sm font-bold text-slate-700">الهالك (كجم) *</label>
                                <input type="number" name="waste_weight_kg" min="0" step="0.01" required class="mt-2 h-12 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10">
                            </div>

                            <div class="xl:col-span-4">
                                <label class="block text-sm font-bold text-slate-700">ملاحظات</label>
                                <input type="text" name="notes" maxlength="2000" class="mt-2 h-12 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10">
                            </div>

                            <div class="xl:col-span-12 flex justify-end">
                                <button type="submit" class="inline-flex min-h-12 items-center justify-center rounded-xl bg-emerald-700 px-6 text-sm font-black text-white shadow-lg shadow-emerald-700/20 transition hover:bg-emerald-800">تسجيل دفعة تصنيع</button>
                            </div>
                        </div>
                    </form>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-300 px-5 py-12 text-center">
                        <p class="font-black text-slate-800">لا يوجد رمان متاح للتفصيص</p>
                        <p class="mt-2 text-sm text-slate-500">انقل الحمولة إلى براد مفتوح أولًا لتظهر هنا.</p>
                    </div>
                @endforelse
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-5">
            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6 xl:col-span-2">
                <h2 class="text-xl font-black text-slate-900">مخزون ناتج التفصيص</h2>
                <p class="mt-1 text-sm text-slate-500">المتاح حاليًا من المنتجات الناتجة عن التفصيص والتصنيع.</p>
                <div class="mt-5 space-y-3">
                    @forelse ($products as $product)
                        <div class="rounded-2xl border border-slate-100 bg-slate-50 px-4 py-4">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="font-black text-slate-900">{{ $product->name }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $product->unit }}</p>
                                </div>
                                <p class="text-xl font-black text-emerald-700">{{ number_format((float) ($product->stock?->quantity ?? 0), 2) }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-2xl bg-slate-50 px-4 py-10 text-center text-sm text-slate-500">لا يوجد مخزون ناتج حاليًا.</div>
                    @endforelse
                </div>
            </div>

            <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm xl:col-span-3">
                <div class="border-b border-slate-100 p-5 sm:p-6"><h2 class="text-xl font-black text-slate-900">سجل التفصيص</h2><p class="mt-1 text-sm text-slate-500">آخر 30 عملية: الحمولة، الداخل، الناتج، الهالك والمستخدم.</p></div>
                <div class="overflow-x-auto">
                    <table class="min-w-[920px] w-full text-right text-sm">
                        <thead class="bg-slate-50 text-slate-500"><tr><th class="whitespace-nowrap px-4 py-3">التاريخ</th><th class="whitespace-nowrap px-4 py-3">الحمولة</th><th class="whitespace-nowrap px-4 py-3">العملية</th><th class="whitespace-nowrap px-4 py-3">الداخل</th><th class="whitespace-nowrap px-4 py-3">الناتج</th><th class="whitespace-nowrap px-4 py-3">الهالك</th><th class="whitespace-nowrap px-4 py-3">المسجل</th></tr></thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($recentBatches as $batch)
                                <tr><td class="whitespace-nowrap px-4 py-3 text-slate-600">{{ optional($batch->processed_at)->format('Y-m-d H:i') }}</td><td class="whitespace-nowrap px-4 py-3 font-bold text-slate-900">{{ $batch->pomegranateLoad?->load_number ?? '—' }}</td><td class="whitespace-nowrap px-4 py-3">{{ $batch->process_type === 'juice' ? 'عصير' : 'تفصيص' }}</td><td class="whitespace-nowrap px-4 py-3">{{ number_format((float) $batch->input_weight_kg, 1) }} كجم</td><td class="whitespace-nowrap px-4 py-3 text-emerald-700">{{ number_format((float) $batch->output_weight_kg, 1) }} كجم</td><td class="whitespace-nowrap px-4 py-3 text-red-600">{{ number_format((float) $batch->waste_weight_kg, 1) }} كجم</td><td class="whitespace-nowrap px-4 py-3 text-slate-600">{{ $batch->recorder?->name ?? '—' }}</td></tr>
                            @empty
                                <tr><td colspan="7" class="px-4 py-12 text-center text-sm text-slate-500">لا توجد عمليات تفصيص مسجلة حتى الآن.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>
</x-layouts.app>
