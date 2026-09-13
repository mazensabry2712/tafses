<x-layouts.app title="البيانات الأساسية">
    <main dir="rtl" class="mx-auto max-w-7xl space-y-6 px-4 py-5 sm:px-6 sm:py-7 lg:px-8">
        <section class="overflow-hidden rounded-3xl bg-gradient-to-br from-slate-950 via-emerald-950 to-slate-900 p-5 text-white shadow-xl sm:p-7">
            <div class="inline-flex rounded-full bg-white/10 px-3 py-1 text-xs font-semibold text-emerald-100 ring-1 ring-white/10">الإدارة / البيانات الأساسية</div>
            <h1 class="mt-3 text-2xl font-black tracking-tight sm:text-3xl">البيانات الأساسية</h1>
            <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-200/80 sm:text-base">المركبات والبرادات وأصحاب العهد ومعايير الأقفاص والمنتجات النهائية.</p>
        </section>

        @if($errors->any())
            <div class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800 shadow-sm">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>
        @endif

        <div class="grid gap-6 xl:grid-cols-2">
            <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <h2 class="text-xl font-black text-slate-900">المركبات</h2>
                <form method="POST" action="{{ route('master-data.vehicles.store') }}" class="mt-5 grid gap-3 sm:grid-cols-2">@csrf
                    <input name="plate_number" required placeholder="رقم اللوحة" class="h-11 rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm outline-none focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/10">
                    <input name="type" placeholder="النوع" class="h-11 rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm outline-none focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/10">
                    <input name="driver_name" placeholder="اسم السائق" class="h-11 rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm outline-none focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/10">
                    <input name="driver_phone" placeholder="هاتف السائق" inputmode="tel" class="h-11 rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm outline-none focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/10">
                    <input name="notes" placeholder="ملاحظات" class="h-11 rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm outline-none focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 sm:col-span-2">
                    <button class="min-h-11 rounded-xl bg-emerald-700 px-4 py-2 text-sm font-black text-white transition hover:bg-emerald-800 sm:col-span-2">إضافة مركبة</button>
                </form>
                <div class="mt-5 space-y-2">
                    @foreach($vehicles as $v)
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-3 text-sm"><b class="text-slate-900">{{ $v->plate_number }}</b> <span class="text-slate-500">— {{ $v->type ?: 'بدون نوع' }} — {{ $v->driver_name ?: 'بدون سائق' }}</span></div>
                    @endforeach
                </div>
            </section>

            <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <h2 class="text-xl font-black text-slate-900">البرادات</h2>
                <form method="POST" action="{{ route('master-data.cold-stores.store') }}" class="mt-5 grid gap-3 sm:grid-cols-2">@csrf
                    <input name="name" required placeholder="اسم البراد" class="h-11 rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm outline-none focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/10">
                    <input name="code" required placeholder="الكود" class="h-11 rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm outline-none focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/10">
                    <select name="crate_standard_id" class="h-11 rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm outline-none focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 sm:col-span-2"><option value="">بدون معيار</option>@foreach($crateStandards->where('is_active',true) as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach</select>
                    <button class="min-h-11 rounded-xl bg-emerald-700 px-4 py-2 text-sm font-black text-white transition hover:bg-emerald-800 sm:col-span-2">إضافة براد</button>
                </form>
                <div class="mt-5 space-y-2">
                    @foreach($coldStores as $c)
                        <div class="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-3 text-sm sm:flex-row sm:items-center sm:justify-between"><span><b class="text-slate-900">{{ $c->name }}</b> <span class="text-slate-500">— {{ $c->code }} — {{ $c->current_crates_count }} قفص</span></span>@if($c->is_active)<form method="POST" action="{{ route('master-data.cold-stores.close',$c) }}">@csrf<button class="rounded-xl border border-red-200 bg-white px-3 py-2 text-xs font-bold text-red-700 hover:bg-red-50">إغلاق</button></form>@else<span class="text-sm font-semibold text-slate-500">مغلق</span>@endif</div>
                    @endforeach
                </div>
            </section>

            <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <h2 class="text-xl font-black text-slate-900">أصحاب العهد</h2>
                <form method="POST" action="{{ route('master-data.custodians.store') }}" class="mt-5 grid gap-3 sm:grid-cols-2">@csrf
                    <input name="name" required placeholder="الاسم" class="h-11 rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm outline-none focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/10">
                    <input name="phone" placeholder="الهاتف" inputmode="tel" class="h-11 rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm outline-none focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/10">
                    <button class="min-h-11 rounded-xl bg-emerald-700 px-4 py-2 text-sm font-black text-white transition hover:bg-emerald-800 sm:col-span-2">إضافة</button>
                </form>
                <div class="mt-5 space-y-2">
                    @foreach($custodians as $c)
                        <div class="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-3 text-sm sm:flex-row sm:items-center sm:justify-between"><span>{{ $c->name }} — {{ $c->is_active ? 'نشط' : 'معطل' }}</span><form method="POST" action="{{ route('master-data.custodians.toggle',$c) }}">@csrf<button class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-700 hover:bg-slate-100">{{ $c->is_active ? 'تعطيل' : 'تفعيل' }}</button></form></div>
                    @endforeach
                </div>
            </section>

            <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <h2 class="text-xl font-black text-slate-900">معايير الأقفاص</h2>
                <form method="POST" action="{{ route('master-data.crate-standards.store') }}" class="mt-5 grid gap-3 sm:grid-cols-2">@csrf
                    <input name="name" required placeholder="اسم المعيار" class="h-11 rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm outline-none focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 sm:col-span-2">
                    <input name="gross_weight_kg" type="number" step="0.001" min="0.001" required placeholder="إجمالي كجم" inputmode="decimal" class="h-11 rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm outline-none focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/10">
                    <input name="tare_weight_kg" type="number" step="0.001" min="0" required placeholder="تارة كجم" inputmode="decimal" class="h-11 rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm outline-none focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/10">
                    <button class="min-h-11 rounded-xl bg-emerald-700 px-4 py-2 text-sm font-black text-white transition hover:bg-emerald-800 sm:col-span-2">إضافة معيار</button>
                </form>
                <div class="mt-5 space-y-2">
                    @foreach($crateStandards as $s)
                        <div class="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-3 text-sm sm:flex-row sm:items-center sm:justify-between"><span>{{ $s->name }} — صافي {{ number_format($s->netWeightKg(),3) }} كجم</span><form method="POST" action="{{ route('master-data.crate-standards.toggle',$s) }}">@csrf<button class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-700 hover:bg-slate-100">{{ $s->is_active ? 'تعطيل' : 'تفعيل' }}</button></form></div>
                    @endforeach
                </div>
            </section>

            <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6 xl:col-span-2">
                <h2 class="text-xl font-black text-slate-900">المنتجات النهائية</h2>
                <form method="POST" action="{{ route('master-data.products.store') }}" class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">@csrf
                    <input name="name" required placeholder="اسم المنتج" class="h-11 rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm outline-none focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/10">
                    <input name="code" required placeholder="الكود" class="h-11 rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm outline-none focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/10">
                    <input name="type" required placeholder="النوع" class="h-11 rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm outline-none focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/10">
                    <input name="unit" required value="kg" placeholder="الوحدة" class="h-11 rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm outline-none focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/10">
                    <button class="min-h-11 rounded-xl bg-emerald-700 px-4 py-2 text-sm font-black text-white transition hover:bg-emerald-800 sm:col-span-2 lg:col-span-4">إضافة منتج</button>
                </form>
                <div class="mt-5 space-y-2">
                    @foreach($products as $p)
                        <div class="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-3 text-sm sm:flex-row sm:items-center sm:justify-between"><span>{{ $p->name }} — {{ $p->code }} — {{ number_format((float)($p->stock?->quantity ?? 0),3) }} {{ $p->unit }}</span><form method="POST" action="{{ route('master-data.products.toggle',$p) }}">@csrf<button class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-700 hover:bg-slate-100">{{ $p->is_active ? 'تعطيل' : 'تفعيل' }}</button></form></div>
                    @endforeach
                </div>
            </section>
        </div>
    </main>
</x-layouts.app>
