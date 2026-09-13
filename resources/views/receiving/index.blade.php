<x-layouts.app title="استلام شحنة">
    <main dir="rtl" class="mx-auto max-w-6xl space-y-6 px-4 py-5 sm:px-6 sm:py-7 lg:px-8">
        <section class="overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-950 via-emerald-900 to-slate-900 p-5 text-white shadow-xl sm:p-7">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <div class="inline-flex items-center rounded-full bg-white/10 px-3 py-1 text-xs font-semibold text-emerald-100 ring-1 ring-white/10">التشغيل / الاستلام</div>
                    <h1 class="mt-3 text-2xl font-black tracking-tight sm:text-3xl">استلام شحنة رمان</h1>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-emerald-50/80 sm:text-base">تسجيل الشحنة والبيانات الأساسية قبل نقل الأقفاص إلى المخزن المبرد.</p>
                </div>
                <a href="{{ route('dashboard') }}" class="inline-flex w-full items-center justify-center rounded-xl bg-white px-4 py-3 text-sm font-bold text-slate-900 transition hover:bg-emerald-50 sm:w-auto">← لوحة التحكم</a>
            </div>
        </section>

        @if ($errors->any())
            <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-4 text-sm text-red-800 shadow-sm">
                <p class="font-bold">راجع البيانات التالية:</p>
                <ul class="mt-2 list-disc space-y-1 pr-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('receiving.loads.store') }}" class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            @csrf
            <div class="border-b border-slate-100 px-4 py-4 sm:px-6">
                <h2 class="text-lg font-black text-slate-900">بيانات الشحنة</h2>
                <p class="mt-1 text-sm text-slate-500">أدخل البيانات الأساسية بدقة لتسهيل المتابعة والمراجعة.</p>
            </div>

            <div class="grid gap-5 p-4 sm:p-6 md:grid-cols-2">
                <div>
                    <label for="load_number" class="block text-sm font-bold text-slate-700">رقم الشحنة <span class="text-red-500">*</span></label>
                    <input id="load_number" name="load_number" value="{{ old('load_number') }}" required autocomplete="off" class="mt-2 h-12 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/10">
                </div>
                <div>
                    <label for="received_at" class="block text-sm font-bold text-slate-700">تاريخ ووقت الاستلام</label>
                    <input id="received_at" type="datetime-local" name="received_at" value="{{ old('received_at', now()->format('Y-m-d\\TH:i')) }}" class="mt-2 h-12 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-900 outline-none transition focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/10">
                </div>
                <div>
                    <label for="supplier_id" class="block text-sm font-bold text-slate-700">المورد</label>
                    <select id="supplier_id" name="supplier_id" class="mt-2 h-12 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-900 outline-none transition focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/10">
                        <option value="">بدون تحديد</option>
                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" @selected(old('supplier_id') == $supplier->id)>{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="vehicle_id" class="block text-sm font-bold text-slate-700">السيارة</label>
                    <select id="vehicle_id" name="vehicle_id" class="mt-2 h-12 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-900 outline-none transition focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/10">
                        <option value="">بدون تحديد</option>
                        @foreach ($vehicles as $vehicle)
                            <option value="{{ $vehicle->id }}" @selected(old('vehicle_id') == $vehicle->id)>{{ $vehicle->plate_number }}{{ $vehicle->driver_name ? ' — '.$vehicle->driver_name : '' }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="crates_count" class="block text-sm font-bold text-slate-700">عدد الأقفاص <span class="text-red-500">*</span></label>
                    <input id="crates_count" type="number" min="1" name="crates_count" value="{{ old('crates_count') }}" required inputmode="numeric" class="mt-2 h-12 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-900 outline-none transition focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/10">
                </div>
                <div>
                    <label for="weight_kg" class="block text-sm font-bold text-slate-700">الوزن بالكيلو <span class="text-red-500">*</span></label>
                    <input id="weight_kg" type="number" min="0.001" step="0.001" name="weight_kg" value="{{ old('weight_kg') }}" required inputmode="decimal" class="mt-2 h-12 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-900 outline-none transition focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/10">
                </div>
                <div class="md:col-span-2">
                    <label for="notes" class="block text-sm font-bold text-slate-700">ملاحظات</label>
                    <textarea id="notes" name="notes" rows="5" placeholder="أي ملاحظات خاصة بالشحنة..." class="mt-2 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/10">{{ old('notes') }}</textarea>
                </div>
            </div>

            <div class="flex flex-col-reverse gap-3 border-t border-slate-100 bg-slate-50/80 px-4 py-4 sm:flex-row sm:items-center sm:justify-end sm:px-6">
                <a href="{{ route('dashboard') }}" class="inline-flex min-h-12 w-full items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-100 sm:w-auto">إلغاء</a>
                <button type="submit" class="inline-flex min-h-12 w-full items-center justify-center rounded-xl bg-emerald-700 px-6 py-3 text-sm font-black text-white shadow-sm transition hover:bg-emerald-800 focus:outline-none focus:ring-4 focus:ring-emerald-500/20 sm:w-auto">حفظ الشحنة</button>
            </div>
        </form>
    </main>
</x-layouts.app>
