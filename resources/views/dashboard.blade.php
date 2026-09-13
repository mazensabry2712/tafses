<x-layouts.app title="لوحة التشغيل">
    <main dir="rtl" class="mx-auto w-full max-w-7xl space-y-6 px-4 py-5 sm:px-6 sm:py-7 lg:px-8 lg:py-8">
        <section class="rounded-3xl bg-gradient-to-l from-slate-950 via-slate-900 to-slate-800 p-5 text-white shadow-soft sm:p-7">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-sm font-semibold text-green-400">Tafses ERP</p>
                    <h1 class="mt-2 text-2xl font-black sm:text-3xl">لوحة التشغيل اليومية</h1>
                    <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-300 sm:text-base">كل دورة العمل أمامك في صفحة واحدة: استلام الشحنة، السيارة، البرادات، العُهد، التصنيع، المنتجات، المبيعات والتحصيل، والموردين والمدفوعات.</p>
                </div>
                <div class="flex items-center gap-3 rounded-2xl border border-white/10 bg-white/5 p-3">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-green-600 font-bold">{{ mb_substr(auth()->user()->name, 0, 1) }}</div>
                    <div><p class="text-sm font-bold">{{ auth()->user()->name }}</p><p class="text-xs text-slate-400">{{ auth()->user()->role }}</p></div>
                </div>
            </div>
        </section>

        @if ($errors->any())
            <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-4 text-sm text-red-800">
                <p class="font-bold">راجع البيانات التالية:</p>
                <ul class="mt-2 list-disc space-y-1 pr-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl border bg-white p-5 shadow-sm"><p class="text-sm text-slate-500">الشحنات اليوم</p><p class="mt-2 text-3xl font-black">{{ number_format($report['receiving']['loads_count']) }}</p><p class="mt-1 text-sm text-slate-500">{{ number_format($report['receiving']['crates_count']) }} قفص</p></div>
            <div class="rounded-2xl border bg-white p-5 shadow-sm"><p class="text-sm text-slate-500">استلام اليوم</p><p class="mt-2 text-3xl font-black">{{ number_format($report['receiving']['weight_kg'], 1) }}</p><p class="mt-1 text-sm text-slate-500">كجم</p></div>
            <div class="rounded-2xl border bg-white p-5 shadow-sm"><p class="text-sm text-slate-500">إنتاج اليوم</p><p class="mt-2 text-3xl font-black">{{ number_format($report['processing']['output_weight_kg'], 1) }}</p><p class="mt-1 text-sm text-slate-500">كجم منتج</p></div>
            <div class="rounded-2xl border bg-white p-5 shadow-sm"><p class="text-sm text-slate-500">مبيعات اليوم</p><p class="mt-2 text-3xl font-black">{{ number_format($report['sales']['total_amount'], 2) }}</p><p class="mt-1 text-sm text-slate-500">ج.م</p></div>
        </section>

        @canPermission('receive_loads')
        <section class="rounded-2xl border bg-white p-5 shadow-sm sm:p-6">
            <div class="mb-5 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between"><div><p class="text-sm font-semibold text-green-700">01 · الاستلام</p><h2 class="mt-1 text-xl font-black">استلام شحنة رمان</h2></div><span class="text-xs text-slate-500">تسجيل الشحنة تبدأ منه دورة العمل</span></div>
            <form method="POST" action="{{ route('receiving.loads.store') }}" class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                @csrf
                <input name="load_number" required placeholder="رقم الشحنة *" value="{{ old('load_number') }}" class="field">
                <input name="received_at" type="datetime-local" value="{{ old('received_at', now()->format('Y-m-d\\TH:i')) }}" class="field">
                <select name="supplier_id" class="field"><option value="">المورد</option>@foreach($suppliers as $supplier)<option value="{{ $supplier->id }}">{{ $supplier->name }}</option>@endforeach</select>
                <select name="vehicle_id" class="field"><option value="">السيارة</option>@foreach($vehicles as $vehicle)<option value="{{ $vehicle->id }}">{{ $vehicle->plate_number }}{{ $vehicle->driver_name ? ' — '.$vehicle->driver_name : '' }}</option>@endforeach</select>
                <input name="crates_count" type="number" min="1" required placeholder="عدد الأقفاص *" class="field">
                <input name="weight_kg" type="number" step="0.001" min="0.001" required placeholder="الوزن بالكيلو *" class="field">
                <input name="notes" placeholder="ملاحظات" class="field lg:col-span-2">
                <button class="rounded-2xl bg-slate-950 px-5 py-3 text-sm font-bold text-white hover:bg-slate-800">حفظ الشحنة</button>
            </form>
        </section>
        @endcanPermission

        @canPermission('manage_cold_stores')
        <section class="rounded-2xl border bg-white p-5 shadow-sm sm:p-6">
            <div class="mb-5"><p class="text-sm font-semibold text-blue-700">02 · المخازن</p><h2 class="mt-1 text-xl font-black">السيارة → البراد</h2><p class="mt-1 text-sm text-slate-500">يمكن تقسيم الحمولة على أكثر من براد.</p></div>
            <div class="space-y-4">
                @forelse($loadsOnVehicle as $load)
                    <form method="POST" action="{{ route('warehouse.loads.cold-store', $load) }}" class="grid gap-3 rounded-2xl border bg-slate-50/70 p-4 lg:grid-cols-7">
                        @csrf
                        <div class="lg:col-span-2"><p class="font-bold">{{ $load->load_number }}</p><p class="mt-1 text-xs text-slate-500">{{ $load->supplier?->name ?? 'بدون مورد' }} · {{ $load->vehicle?->plate_number ?? 'بدون سيارة' }}</p><p class="mt-1 text-xs text-slate-500">على السيارة: {{ $load->on_vehicle_crates_count }} قفص · {{ number_format((float)$load->on_vehicle_weight_kg,3) }} كجم</p></div>
                        <select name="cold_store_id" required class="field"><option value="">اختر البراد</option>@foreach($coldStores as $store)<option value="{{ $store->id }}">{{ $store->name }}</option>@endforeach</select>
                        <input name="crates_count" type="number" min="1" max="{{ $load->on_vehicle_crates_count }}" required placeholder="عدد الأقفاص" class="field">
                        <input name="weight_kg" type="number" step="0.001" min="0.001" placeholder="الوزن (اختياري)" class="field">
                        <input name="notes" placeholder="ملاحظات" class="field">
                        <button class="rounded-2xl bg-slate-950 px-4 py-3 text-sm font-bold text-white hover:bg-slate-800">نقل للبراد</button>
                    </form>
                @empty
                    <div class="rounded-2xl bg-slate-50 px-4 py-7 text-center text-sm text-slate-500">لا توجد حمولات على السيارات حاليًا.</div>
                @endforelse
            </div>
        </section>
        @endcanPermission

        @canPermission('manage_custody')
        <section class="grid gap-6 lg:grid-cols-2">
            <div class="rounded-2xl border bg-white p-5 shadow-sm sm:p-6">
                <p class="text-sm font-semibold text-amber-700">03 · العُهد</p><h2 class="mt-1 text-xl font-black">صرف عُهدة</h2><p class="mt-1 text-sm text-slate-500">يمكن تكرار الصرف لنفس الشخص.</p>
                <div class="mt-5 space-y-3">
                    @foreach($coldStores as $store)
                        @foreach($store->stocks->where('crates_count','>',0) as $stock)
                            <form method="POST" action="{{ route('warehouse.custody.issue', [$store, $stock->pomegranateLoad, 0]) }}" class="rounded-2xl border p-3" data-custody-issue-form>
                                @csrf
                                <div class="mb-3 text-xs text-slate-500">{{ $store->name }} · {{ $stock->pomegranateLoad?->load_number }} · {{ $stock->crates_count }} قفص</div>
                                <div class="grid gap-2 sm:grid-cols-3">
                                    <select name="custodian_id" required class="field sm:col-span-2" data-custodian-select>@foreach($custodians as $custodian)<option value="{{ $custodian->id }}">{{ $custodian->name }}</option>@endforeach</select>
                                    <input name="crates_count" type="number" min="1" max="{{ $stock->crates_count }}" required placeholder="الأقفاص" class="field">
                                </div>
                                <input type="hidden" name="weight_kg" value="">
                                <button class="mt-2 w-full rounded-2xl bg-slate-950 px-4 py-3 text-sm font-bold text-white">صرف العُهدة</button>
                            </form>
                        @endforeach
                    @endforeach
                    @if($custodians->isEmpty())<p class="text-sm text-slate-500">أضف أصحاب العهد من البيانات الأساسية أولًا.</p>@endif
                </div>
            </div>

            <div class="rounded-2xl border bg-white p-5 shadow-sm sm:p-6">
                <p class="text-sm font-semibold text-rose-700">04 · العُهد</p><h2 class="mt-1 text-xl font-black">العُهد المفتوحة</h2><p class="mt-1 text-sm text-slate-500">راجع الأرصدة المفتوحة وسجّل الإرجاع من نفس الشاشة.</p>
                <div class="mt-5 space-y-3">
                    @foreach($coldStores as $store)
                        @php
                            $holdings = $store->stocks->filter(fn($s) => $s->crates_count > 0)->flatMap(function($stock) use ($store) {
                                return \App\Models\CustodyTransaction::query()->where('cold_store_id',$store->id)->where('pomegranate_load_id',$stock->pomegranate_load_id)->where('type','issue')->select('custodian_id','pomegranate_load_id')->distinct()->get()->map(function($row) use ($store,$stock){
                                    $issued=(int)\App\Models\CustodyTransaction::query()->where('cold_store_id',$store->id)->where('pomegranate_load_id',$row->pomegranate_load_id)->where('custodian_id',$row->custodian_id)->where('type','issue')->sum('crates_count');
                                    $returned=(int)\App\Models\CustodyTransaction::query()->where('cold_store_id',$store->id)->where('pomegranate_load_id',$row->pomegranate_load_id)->where('custodian_id',$row->custodian_id)->whereIn('type',['return_to_vehicle','return_to_store'])->sum('crates_count');
                                    return compact('row','issued','returned');
                                });
                            })->filter(fn($h) => $h['issued'] > $h['returned'])->values();
                        @endphp
                        @foreach($holdings as $holding)
                            @php $custodian=$custodians->firstWhere('id',$holding['row']->custodian_id); $load=$loadsOnVehicle->firstWhere('id',$holding['row']->pomegranate_load_id) ?? \App\Models\PomegranateLoad::find($holding['row']->pomegranate_load_id); @endphp
                            <form method="POST" action="{{ route('warehouse.custody.return', [$store, $load, $custodian]) }}" class="rounded-2xl border p-3">
                                @csrf
                                <div class="mb-3"><p class="font-bold">{{ $custodian?->name ?? 'أمين عهدة' }}</p><p class="text-xs text-slate-500">{{ $store->name }} · {{ $load?->load_number }} · المتبقي {{ $holding['issued']-$holding['returned'] }} قفص</p></div>
                                <div class="grid gap-2 sm:grid-cols-3"><input name="crates_count" type="number" min="1" max="{{ $holding['issued']-$holding['returned'] }}" required placeholder="الأقفاص" class="field"><select name="destination" required class="field"><option value="vehicle">للسيارة</option><option value="cold_store">للبراد</option></select><button class="rounded-2xl bg-slate-950 px-4 py-3 text-sm font-bold text-white">إرجاع</button></div>
                                <input type="hidden" name="weight_kg" value=""><input type="hidden" name="notes" value="">
                            </form>
                        @endforeach
                    @endforeach
                    <a href="{{ route('warehouse.custody') }}" class="inline-flex rounded-2xl border px-4 py-3 text-sm font-bold hover:bg-slate-50">عرض سجل العُهد الكامل</a>
                </div>
            </div>
        </section>
        @endcanPermission

        @canPermission('process_pomegranates')
        <section class="rounded-2xl border bg-white p-5 shadow-sm sm:p-6">
            <p class="text-sm font-semibold text-green-700">05 · التصنيع</p><h2 class="mt-1 text-xl font-black">تحويل الرمان إلى حبوب أو عصير</h2><p class="mt-1 text-sm text-slate-500">اختر البراد والحمولة وسجل الداخل والناتج والهالك.</p>
            <div class="mt-5 space-y-3">
                @forelse($processingLoads as $load)
                    <form method="POST" action="{{ route('processing.store', $load) }}" class="grid gap-3 rounded-2xl border p-4 lg:grid-cols-8">
                        @csrf
                        <div class="lg:col-span-2"><p class="font-bold">{{ $load->load_number }}</p><p class="text-xs text-slate-500">المتاح: {{ $load->available_crates_count }} قفص · {{ number_format((float)$load->available_weight_kg,3) }} كجم</p></div>
                        <select name="cold_store_id" required class="field lg:col-span-2"><option value="">اختر البراد</option>@foreach($load->coldStoreStocks->filter(fn($s)=>$s->coldStore && $s->crates_count>0 && (float)$s->weight_kg>0) as $stock)<option value="{{ $stock->coldStore->id }}">{{ $stock->coldStore->name }} · {{ $stock->crates_count }} قفص / {{ number_format((float)$stock->weight_kg,3) }} كجم</option>@endforeach</select>
                        <select name="process_type" required class="field"><option value="peeling">تقشير / حبوب</option><option value="juice">عصير</option></select>
                        <input name="input_crates_count" type="number" min="1" required placeholder="أقفاص الداخل" class="field">
                        <input name="input_weight_kg" type="number" step="0.001" min="0.001" required placeholder="الداخل كجم" class="field">
                        <input name="output_weight_kg" type="number" step="0.001" min="0" required placeholder="الناتج كجم" class="field">
                        <input name="waste_weight_kg" type="number" step="0.001" min="0" placeholder="الهالك كجم" class="field">
                        <div class="flex gap-2 lg:col-span-8"><input name="notes" placeholder="ملاحظات" class="field flex-1"><button class="rounded-2xl bg-slate-950 px-5 py-3 text-sm font-bold text-white">تسجيل التصنيع</button></div>
                    </form>
                @empty<div class="rounded-2xl bg-slate-50 px-4 py-7 text-center text-sm text-slate-500">لا توجد حمولات متاحة للتصنيع.</div>@endforelse
            </div>
        </section>
        @endcanPermission

        <section class="grid gap-6 xl:grid-cols-2">
            @canPermission('manage_sales')
            <section class="rounded-2xl border bg-white p-5 shadow-sm sm:p-6">
                <p class="text-sm font-semibold text-emerald-700">06 · المبيعات</p><h2 class="mt-1 text-xl font-black">فاتورة بيع جديدة</h2>
                <form method="POST" action="{{ route('sales.store', $customers->first() ?? 0) }}" id="dashboard-sale-form" class="mt-5 space-y-3">
                    @csrf
                    <select name="customer_id" required class="field" onchange="document.getElementById('dashboard-sale-form').action='{{ url('/sales/customers') }}/'+this.value+'/invoices'"><option value="">اختر العميل</option>@foreach($customers as $customer)<option value="{{ $customer->id }}">{{ $customer->name }}</option>@endforeach</select>
                    <div class="grid gap-3 sm:grid-cols-2"><input name="invoice_number" required placeholder="رقم الفاتورة" class="field"><input name="paid_amount" type="number" min="0" step="0.01" placeholder="المدفوع" class="field"></div>
                    <select name="items[0][finished_product_id]" required class="field"><option value="">اختر المنتج</option>@foreach($products->filter(fn($p)=>(float)($p->stock?->quantity??0)>0) as $product)<option value="{{ $product->id }}">{{ $product->name }} · متاح {{ number_format((float)($product->stock?->quantity??0),3) }} {{ $product->unit }}</option>@endforeach</select>
                    <div class="grid gap-3 sm:grid-cols-2"><input name="items[0][quantity]" type="number" min="0.001" step="0.001" required placeholder="الكمية" class="field"><input name="items[0][unit_price]" type="number" min="0" step="0.01" required placeholder="سعر الوحدة" class="field"></div>
                    <button class="w-full rounded-2xl bg-slate-950 px-5 py-3 text-sm font-bold text-white">حفظ الفاتورة</button>
                </form>
            </section>
            @endcanPermission

            @canPermission('manage_suppliers')
            <section class="rounded-2xl border bg-white p-5 shadow-sm sm:p-6">
                <p class="text-sm font-semibold text-amber-700">07 · المشتريات</p><h2 class="mt-1 text-xl font-black">تسجيل شراء حمولة</h2>
                <div class="mt-5 space-y-3">
                    @php $purchaseLoads=$loadsOnVehicle->filter(fn($l)=>$l->supplier_id && !\App\Models\PomegranatePurchase::where('pomegranate_load_id',$l->id)->exists()); @endphp
                    @forelse($purchaseLoads as $load)
                        <form method="POST" action="{{ route('suppliers.purchases.store', $load->supplier) }}" class="rounded-2xl border p-4">
                            @csrf<input type="hidden" name="pomegranate_load_id" value="{{ $load->id }}">
                            <p class="font-bold">{{ $load->load_number }} · {{ $load->supplier->name }}</p><p class="text-xs text-slate-500">{{ $load->loaded_crates_count }} قفص · {{ number_format((float)$load->loaded_weight_kg,1) }} كجم</p>
                            <div class="mt-3 grid gap-2 sm:grid-cols-3"><select name="pricing_unit" class="field"><option value="kg">بالكيلو</option><option value="crate">بالقفص</option><option value="fixed">مبلغ ثابت</option></select><input name="unit_price" type="number" step="0.001" min="0.001" required placeholder="السعر" class="field"><input name="initial_paid" type="number" step="0.001" min="0" placeholder="دفعة أولى" class="field"></div>
                            <button class="mt-2 w-full rounded-2xl bg-slate-950 px-4 py-3 text-sm font-bold text-white">حفظ الشراء</button>
                        </form>
                    @empty<div class="rounded-2xl bg-slate-50 px-4 py-7 text-center text-sm text-slate-500">لا توجد حمولات جاهزة للشراء.</div>@endforelse
                </div>
            </section>
            @endcanPermission
        </section>

        <section class="grid gap-6 xl:grid-cols-2">
            @canPermission('manage_payments')
            <section class="rounded-2xl border bg-white p-5 shadow-sm sm:p-6"><p class="text-sm font-semibold text-emerald-700">08 · تحصيل العملاء</p><h2 class="mt-1 text-xl font-black">المبيعات غير المسددة</h2><div class="mt-5 space-y-3">@forelse($sales as $sale)<form method="POST" action="{{ route('sales.payment', [$sale->customer, $sale]) }}" class="grid gap-2 rounded-2xl border p-3 sm:grid-cols-4">@csrf<div class="sm:col-span-2"><p class="font-bold">{{ $sale->invoice_number }}</p><p class="text-xs text-slate-500">{{ $sale->customer->name }} · متبقي {{ number_format($sale->balance(),2) }} ج.م</p></div><input name="amount" type="number" min="0.01" step="0.01" max="{{ $sale->balance() }}" required placeholder="المبلغ" class="field"><button class="rounded-2xl bg-slate-950 px-4 py-3 text-sm font-bold text-white">تحصيل</button><input type="hidden" name="payment_method" value="cash"><input type="hidden" name="notes" value="">@csrf</form>@empty<div class="rounded-2xl bg-slate-50 px-4 py-7 text-center text-sm text-slate-500">لا توجد فواتير مستحقة حاليًا.</div>@endforelse</div></section>
            <section class="rounded-2xl border bg-white p-5 shadow-sm sm:p-6"><p class="text-sm font-semibold text-amber-700">09 · سداد الموردين</p><h2 class="mt-1 text-xl font-black">المشتريات غير المسددة</h2><div class="mt-5 space-y-3">@forelse($purchases as $purchase)<form method="POST" action="{{ route('suppliers.purchases.payments.store', [$purchase->supplier, $purchase]) }}" class="grid gap-2 rounded-2xl border p-3 sm:grid-cols-4">@csrf<div class="sm:col-span-2"><p class="font-bold">{{ $purchase->supplier->name }}</p><p class="text-xs text-slate-500">{{ $purchase->pomegranateLoad?->load_number }} · متبقي {{ number_format($purchase->balance(),2) }} ج.م</p></div><input name="amount" type="number" min="0.01" step="0.01" max="{{ $purchase->balance() }}" required placeholder="المبلغ" class="field"><select name="payment_method" class="field"><option value="cash">نقدي</option><option value="bank">بنك</option><option value="transfer">تحويل</option><option value="other">أخرى</option></select><input name="notes" placeholder="ملاحظات" class="field sm:col-span-2"><button class="rounded-2xl bg-slate-950 px-4 py-3 text-sm font-bold text-white">سداد</button></form>@empty<div class="rounded-2xl bg-slate-50 px-4 py-7 text-center text-sm text-slate-500">لا توجد مشتريات مستحقة حاليًا.</div>@endforelse</div></section>
            @endcanPermission
        </section>

        <section class="rounded-2xl border bg-white p-5 shadow-sm sm:p-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"><div><p class="text-sm font-semibold text-violet-700">10 · المتابعة</p><h2 class="mt-1 text-xl font-black">ملخص المخزون والحسابات</h2></div><div class="flex flex-wrap gap-2"><a href="{{ route('reports.index') }}" class="rounded-xl border px-4 py-2 text-sm font-bold hover:bg-slate-50">التقارير</a>@canPermission('manage_cold_stores')<a href="{{ route('warehouse') }}" class="rounded-xl border px-4 py-2 text-sm font-bold hover:bg-slate-50">تفاصيل المخازن</a>@endcanPermission</div></div>
            <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-4"><div class="rounded-2xl bg-slate-50 p-4"><p class="text-sm text-slate-500">مخزون البرادات</p><p class="mt-2 text-2xl font-black">{{ number_format(collect($report['cold_stores'])->sum('current_crates_count')) }} قفص</p></div><div class="rounded-2xl bg-slate-50 p-4"><p class="text-sm text-slate-500">عُهدة مفتوحة</p><p class="mt-2 text-2xl font-black">{{ number_format(collect($report['cold_stores'])->sum('custody_outstanding_crates')) }} قفص</p></div><div class="rounded-2xl bg-slate-50 p-4"><p class="text-sm text-slate-500">متبقي العملاء</p><p class="mt-2 text-2xl font-black">{{ number_format($report['sales']['balance_due'],2) }} ج.م</p></div><div class="rounded-2xl bg-slate-50 p-4"><p class="text-sm text-slate-500">متبقي الموردين</p><p class="mt-2 text-2xl font-black">{{ number_format($report['purchases']['balance_due'],2) }} ج.م</p></div></div>
        </section>
    </main>

    <style>
        .field { display:block; width:100%; border:1px solid rgb(226 232 240); border-radius:1rem; background:rgb(248 250 252); padding:.8rem 1rem; font-size:.875rem; outline:none; }
        .field:focus { border-color:rgb(34 197 94); background:white; box-shadow:0 0 0 4px rgb(34 197 94 / .10); }
    </style>
    <script>
        document.querySelectorAll('[data-custody-issue-form]').forEach((form) => {
            const select = form.querySelector('[data-custodian-select]');
            const updateAction = () => { form.action = form.action.replace(/custodians\/0\/issue$/, `custodians/${select.value}/issue`); };
            select.addEventListener('change', updateAction); updateAction();
        });
    </script>
</x-layouts.app>
