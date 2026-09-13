<x-layouts.app title="المخازن والثلاجات">
    <main dir="rtl" class="mx-auto max-w-7xl space-y-6 px-6 py-8">
        <div>
            <h1 class="text-2xl font-bold">المخازن والثلاجات</h1>
            <p class="mt-1 text-sm text-gray-600">متابعة مخزون كل براد، نقل الحمولة، وإدارة العُهد.</p>
        </div>

        <section class="grid gap-4 md:grid-cols-3">
            @foreach ($coldStores as $store)
                <div class="rounded-xl border bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h2 class="font-semibold">{{ $store->name }}</h2>
                            <p class="text-xs text-gray-500">{{ $store->code }}</p>
                        </div>
                        <span class="rounded-full px-2.5 py-1 text-xs {{ $store->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                            {{ $store->is_active ? 'مفتوح' : 'مغلق' }}
                        </span>
                    </div>
                    <dl class="mt-4 grid grid-cols-2 gap-3 text-sm">
                        <div class="rounded-lg bg-gray-50 p-3"><dt class="text-gray-500">أقفاص</dt><dd class="mt-1 text-lg font-bold">{{ $store->current_crates_count }}</dd></div>
                        <div class="rounded-lg bg-gray-50 p-3"><dt class="text-gray-500">وزن صافي</dt><dd class="mt-1 text-lg font-bold">{{ number_format((float) $store->current_weight_kg, 3) }} كجم</dd></div>
                    </dl>
                    <p class="mt-3 text-xs text-gray-500">
                        المعيار: {{ $store->crateStandard?->gross_weight_kg ?? '-' }} كجم إجمالي / {{ $store->crateStandard?->tare_weight_kg ?? '-' }} كجم فاقد القفص.
                    </p>
                    @if ($store->is_active)
                        <form method="POST" action="{{ route('warehouse.close', $store) }}" class="mt-4">
                            @csrf
                            <button class="w-full rounded-lg border border-red-200 px-3 py-2 text-sm text-red-700 hover:bg-red-50">إغلاق البراد</button>
                        </form>
                    @endif
                </div>
            @endforeach
        </section>

        @canPermission('manage_cold_stores')
        <section class="rounded-xl border bg-white p-6 shadow-sm">
            <div class="mb-4">
                <h2 class="text-lg font-semibold">نقل حمولة من السيارة إلى البراد</h2>
                <p class="text-sm text-gray-500">الوزن اختياري؛ عند تركه فارغًا يستخدم النظام معيار البراد.</p>
            </div>
            @forelse ($loads as $load)
                <form method="POST" action="{{ route('warehouse.loads.cold-store', $load) }}" class="mb-4 grid gap-3 rounded-lg border p-4 md:grid-cols-5">
                    @csrf
                    <div class="md:col-span-2">
                        <div class="font-medium">{{ $load->load_number }}</div>
                        <div class="text-xs text-gray-500">{{ $load->supplier?->name ?? 'بدون مورد' }} — {{ $load->vehicle?->plate_number ?? 'بدون سيارة' }}</div>
                    </div>
                    <select name="cold_store_id" required class="rounded-lg border-gray-300">
                        <option value="">اختر البراد</option>
                        @foreach ($coldStores->where('is_active', true) as $store)
                            <option value="{{ $store->id }}">{{ $store->name }}</option>
                        @endforeach
                    </select>
                    <input name="crates_count" type="number" min="1" max="{{ $load->on_vehicle_crates_count }}" required placeholder="عدد الأقفاص" class="rounded-lg border-gray-300">
                    <div class="flex gap-2">
                        <input name="weight_kg" type="number" step="0.001" min="0.001" placeholder="الوزن كجم" class="w-full rounded-lg border-gray-300">
                        <button class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">نقل</button>
                    </div>
                </form>
            @empty
                <div class="rounded-lg bg-gray-50 px-4 py-6 text-center text-sm text-gray-500">لا توجد حمولات على السيارات حاليًا.</div>
            @endforelse
        </section>
        @endcanPermission

        @canPermission('manage_custody')
        <section class="grid gap-6 lg:grid-cols-2">
            <div class="rounded-xl border bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold">صرف عُهدة</h2>
                <p class="mb-4 text-sm text-gray-500">الصرف يُسجل على نفس الشخص ويمكن تكراره عدة مرات.</p>
                @foreach ($coldStores->where('is_active', true) as $store)
                    @foreach ($store->stocks()->with('pomegranateLoad')->where('crates_count', '>', 0)->get() as $stock)
                        <form method="POST" action="{{ route('warehouse.custody.issue', [$store, $stock->pomegranateLoad, $custodians->first()]) }}" class="mb-3 grid gap-2 md:grid-cols-4">
                            @csrf
                            <select name="custodian_id" required class="rounded-lg border-gray-300 md:col-span-2" onchange="this.form.action=this.form.action.replace(/custodians\/\\d+\/issue$/, 'custodians/' + this.value + '/issue')">
                                @foreach ($custodians as $custodian)
                                    <option value="{{ $custodian->id }}">{{ $custodian->name }}</option>
                                @endforeach
                            </select>
                            <input name="crates_count" type="number" min="1" max="{{ $stock->crates_count }}" required placeholder="أقفاص" class="rounded-lg border-gray-300">
                            <button class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white">صرف {{ $store->name }} / {{ $stock->pomegranateLoad->load_number }}</button>
                            <input type="hidden" name="weight_kg" value="">
                        </form>
                    @endforeach
                @endforeach
                @if ($coldStores->every(fn ($store) => $store->stocks()->where('crates_count', '>', 0)->count() === 0))
                    <p class="text-sm text-gray-500">لا يوجد مخزون متاح للصرف حاليًا.</p>
                @endif
            </div>

            <div class="rounded-xl border bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold">إرجاع عُهدة</h2>
                <p class="mb-4 text-sm text-gray-500">حدد الوجهة: السيارة أو نفس البراد.</p>
                <p class="text-sm text-gray-500">يُستكمل نموذج الإرجاع التفصيلي من سجل العُهد في المرحلة التالية مع عرض الرصيد المفتوح لكل شخص.</p>
            </div>
        </section>
        @endcanPermission

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
