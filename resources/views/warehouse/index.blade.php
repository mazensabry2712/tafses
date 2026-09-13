<x-layouts.app title="المخازن والثلاجات">
    <main dir="rtl" class="mx-auto max-w-7xl space-y-6 px-4 py-5 sm:px-6 sm:py-7 lg:px-8">
        <section class="overflow-hidden rounded-3xl bg-gradient-to-br from-slate-950 via-emerald-950 to-emerald-900 p-5 text-white shadow-xl sm:p-7">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <div class="inline-flex rounded-full bg-white/10 px-3 py-1 text-xs font-semibold text-emerald-100 ring-1 ring-white/10">التشغيل / المخازن</div>
                    <h1 class="mt-3 text-2xl font-black tracking-tight sm:text-3xl">المخازن والثلاجات</h1>
                    <p class="mt-2 text-sm leading-6 text-emerald-50/80 sm:text-base">متابعة مخزون كل براد، نقل الحمولة، وإدارة العُهد من شاشة واحدة.</p>
                </div>
            </div>
        </section>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @forelse ($coldStores as $store)
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <h2 class="truncate text-lg font-black text-slate-900">{{ $store->name }}</h2>
                            <p class="mt-1 text-xs font-semibold text-slate-400">{{ $store->code }}</p>
                        </div>
                        <span class="shrink-0 rounded-full px-3 py-1 text-xs font-bold {{ $store->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                            {{ $store->is_active ? 'مفتوح' : 'مغلق' }}
                        </span>
                    </div>
                    <dl class="mt-5 grid grid-cols-2 gap-3">
                        <div class="rounded-2xl bg-slate-50 p-4">
                            <dt class="text-xs font-semibold text-slate-500">أقفاص</dt>
                            <dd class="mt-1 text-2xl font-black text-slate-900">{{ $store->current_crates_count }}</dd>
                        </div>
                        <div class="rounded-2xl bg-slate-50 p-4">
                            <dt class="text-xs font-semibold text-slate-500">وزن صافي</dt>
                            <dd class="mt-1 text-xl font-black text-slate-900">{{ number_format((float) $store->current_weight_kg, 3) }} <span class="text-xs font-bold text-slate-500">كجم</span></dd>
                        </div>
                    </dl>
                    <p class="mt-4 rounded-xl bg-emerald-50 px-3 py-2 text-xs leading-5 text-emerald-800">
                        المعيار: {{ $store->crateStandard?->gross_weight_kg ?? '-' }} كجم إجمالي / {{ $store->crateStandard?->tare_weight_kg ?? '-' }} كجم وزن القفص.
                    </p>
                    @if ($store->is_active)
                        <form method="POST" action="{{ route('warehouse.close', $store) }}" class="mt-4">
                            @csrf
                            <button class="min-h-11 w-full rounded-xl border border-red-200 bg-white px-3 py-2 text-sm font-bold text-red-700 transition hover:bg-red-50">إغلاق البراد</button>
                        </form>
                    @endif
                </div>
            @empty
                <div class="sm:col-span-2 xl:col-span-3 rounded-2xl border border-dashed bg-white px-4 py-10 text-center text-sm text-slate-500">لا توجد برادات مسجلة حاليًا.</div>
            @endforelse
        </section>

        @canPermission('manage_cold_stores')
        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-4 py-5 sm:px-6">
                <h2 class="text-xl font-black text-slate-900">نقل حمولة من السيارة إلى البراد</h2>
                <p class="mt-1 text-sm leading-6 text-slate-500">الوزن اختياري؛ عند تركه فارغًا يستخدم النظام معيار البراد.</p>
            </div>
            <div class="p-4 sm:p-6">
                @forelse ($loads as $load)
                    <form method="POST" action="{{ route('warehouse.loads.cold-store', $load) }}" class="mb-4 rounded-2xl border border-slate-200 bg-slate-50/70 p-4 last:mb-0 sm:p-5">
                        @csrf
                        <div class="mb-4 flex flex-col gap-2 border-b border-slate-200 pb-4 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <div class="text-base font-black text-slate-900">{{ $load->load_number }}</div>
                                <div class="mt-1 text-xs text-slate-500">{{ $load->supplier?->name ?? 'بدون مورد' }} — {{ $load->vehicle?->plate_number ?? 'بدون سيارة' }}</div>
                            </div>
                            <span class="rounded-xl bg-white px-3 py-2 text-xs font-bold text-slate-600 ring-1 ring-slate-200">على السيارة: {{ $load->on_vehicle_crates_count }} قفص / {{ number_format((float) $load->on_vehicle_weight_kg, 3) }} كجم</span>
                        </div>
                        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
                            <select name="cold_store_id" required class="h-12 rounded-xl border border-slate-200 bg-white px-4 text-sm outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10">
                                <option value="">اختر البراد</option>
                                @foreach ($coldStores->where('is_active', true) as $store)
                                    <option value="{{ $store->id }}">{{ $store->name }}</option>
                                @endforeach
                            </select>
                            <input name="crates_count" type="number" min="1" max="{{ $load->on_vehicle_crates_count }}" required placeholder="عدد الأقفاص" inputmode="numeric" class="h-12 rounded-xl border border-slate-200 bg-white px-4 text-sm outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10">
                            <input name="weight_kg" type="number" step="0.001" min="0.001" placeholder="الوزن كجم" inputmode="decimal" class="h-12 rounded-xl border border-slate-200 bg-white px-4 text-sm outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10">
                            <button class="min-h-12 rounded-xl bg-emerald-700 px-5 py-3 text-sm font-black text-white transition hover:bg-emerald-800 sm:col-span-2 lg:col-span-2">نقل الحمولة</button>
                        </div>
                    </form>
                @empty
                    <div class="rounded-2xl bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">لا توجد حمولات على السيارات حاليًا.</div>
                @endforelse
            </div>
        </section>
        @endcanPermission

        @canPermission('manage_custody')
        <section class="grid gap-6 xl:grid-cols-2">
            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <div class="mb-5">
                    <h2 class="text-xl font-black text-slate-900">صرف عُهدة</h2>
                    <p class="mt-1 text-sm leading-6 text-slate-500">الصرف يُسجل على نفس الشخص ويمكن تكراره عدة مرات.</p>
                </div>
                @if ($custodians->isEmpty())
                    <p class="rounded-2xl bg-slate-50 px-4 py-6 text-sm text-slate-500">لا يوجد أمناء عهد نشطون مسجلون حاليًا.</p>
                @else
                    @foreach ($coldStores->where('is_active', true) as $store)
                        @foreach ($store->stocks()->with('pomegranateLoad')->where('crates_count', '>', 0)->get() as $stock)
                            <form method="POST" action="{{ route('warehouse.custody.issue', [$store, $stock->pomegranateLoad, 0]) }}" class="mb-3 rounded-2xl border border-slate-200 bg-slate-50/70 p-3 last:mb-0" data-custody-issue-form>
                                @csrf
                                <div class="mb-3 text-sm font-bold text-slate-700">{{ $store->name }} / {{ $stock->pomegranateLoad->load_number }}</div>
                                <div class="grid gap-2 sm:grid-cols-2">
                                    <select name="custodian_id" required class="h-11 rounded-xl border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10" data-custodian-select>
                                        @foreach ($custodians as $custodian)
                                            <option value="{{ $custodian->id }}">{{ $custodian->name }}</option>
                                        @endforeach
                                    </select>
                                    <input name="crates_count" type="number" min="1" max="{{ $stock->crates_count }}" required placeholder="عدد الأقفاص" inputmode="numeric" class="h-11 rounded-xl border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10">
                                    <input type="hidden" name="weight_kg" value="">
                                    <button class="min-h-11 rounded-xl bg-slate-900 px-4 py-2 text-sm font-black text-white transition hover:bg-slate-800 sm:col-span-2">صرف العهدة</button>
                                </div>
                            </form>
                        @endforeach
                    @endforeach
                @endif
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <div class="mb-5">
                    <h2 class="text-xl font-black text-slate-900">إرجاع عُهدة</h2>
                    <p class="mt-1 text-sm leading-6 text-slate-500">الإرجاع يدعم الوجهة إلى السيارة أو نفس البراد.</p>
                </div>
                <div class="flex min-h-48 items-center justify-center rounded-2xl border border-dashed bg-slate-50 px-5 text-center">
                    <div>
                        <p class="text-sm font-bold text-slate-600">سجل العُهد المفتوحة ونماذج الإرجاع موجودة في صفحة إدارة العُهد.</p>
                        <a href="{{ route('warehouse.custody') }}" class="mt-4 inline-flex min-h-11 items-center justify-center rounded-xl bg-emerald-700 px-4 py-2 text-sm font-black text-white transition hover:bg-emerald-800">فتح إدارة العُهد</a>
                    </div>
                </div>
            </div>
        </section>
        @endcanPermission

        @if ($errors->any())
            <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-4 text-sm text-red-800 shadow-sm">
                <ul class="list-disc space-y-1 pr-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </main>

    <script>
        document.querySelectorAll('[data-custody-issue-form]').forEach((form) => {
            const select = form.querySelector('[data-custodian-select]');
            const updateAction = () => {
                form.action = form.action.replace(/custodians\/0\/issue$/, `custodians/${select.value}/issue`);
            };
            select.addEventListener('change', updateAction);
            updateAction();
        });
    </script>
</x-layouts.app>
