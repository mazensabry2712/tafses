<x-layouts.app title="إدارة العُهد">
    <main dir="rtl" class="mx-auto max-w-7xl space-y-6 px-4 py-5 sm:px-6 sm:py-7 lg:px-8">
        <section class="overflow-hidden rounded-3xl bg-gradient-to-br from-slate-950 via-slate-900 to-emerald-950 p-5 text-white shadow-xl sm:p-7">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <div class="inline-flex rounded-full bg-white/10 px-3 py-1 text-xs font-semibold text-emerald-100 ring-1 ring-white/10">التشغيل / العُهد</div>
                    <h1 class="mt-3 text-2xl font-black tracking-tight sm:text-3xl">إدارة العُهد</h1>
                    <p class="mt-2 text-sm leading-6 text-slate-200/80 sm:text-base">متابعة كل شخص، المصروف، المرتجع، والرصيد المفتوح مع سجل الحركات.</p>
                </div>
                <a href="{{ route('warehouse') }}" class="inline-flex w-full items-center justify-center rounded-xl bg-white px-4 py-3 text-sm font-bold text-slate-900 transition hover:bg-slate-100 sm:w-auto">العودة للمخازن</a>
            </div>
        </section>

        @forelse ($summaries as $summary)
            <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="flex flex-col gap-4 border-b border-slate-100 px-4 py-5 sm:px-6 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h2 class="text-xl font-black text-slate-900">{{ $summary['cold_store'] }}</h2>
                        @if ($summary['crate_standard'])
                            <p class="mt-1 text-xs leading-5 text-slate-500">معيار القفص: {{ $summary['crate_standard']['gross_weight_kg'] }} كجم إجمالي − {{ $summary['crate_standard']['tare_weight_kg'] }} كجم فارغ = {{ $summary['crate_standard']['net_weight_kg'] }} كجم صافي.</p>
                        @endif
                    </div>
                    <div class="grid grid-cols-2 gap-2 sm:flex sm:flex-wrap">
                        <span class="rounded-xl bg-slate-50 px-3 py-2 text-center text-xs text-slate-600">مخزون البراد: <strong class="text-slate-900">{{ $summary['current']['crates_count'] }}</strong> قفص</span>
                        <span class="rounded-xl bg-red-50 px-3 py-2 text-center text-xs text-red-700">الرصيد المفتوح: <strong>{{ $summary['total']['outstanding_crates'] }}</strong> قفص</span>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-[760px] w-full text-right text-sm">
                        <thead class="bg-slate-50 text-slate-500">
                            <tr>
                                <th class="px-4 py-3 font-bold sm:px-6">أمين العهدة</th>
                                <th class="px-4 py-3 font-bold">المصروف</th>
                                <th class="px-4 py-3 font-bold">المرتجع</th>
                                <th class="px-4 py-3 font-bold">المتبقي</th>
                                <th class="px-4 py-3 font-bold">الحركات</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($summary['custodians'] as $custodian)
                                <tr class="transition hover:bg-slate-50">
                                    <td class="px-4 py-4 font-bold text-slate-900 sm:px-6">{{ $custodian['name'] }}</td>
                                    <td class="px-4 py-4 text-slate-600">{{ $custodian['issued_crates'] }} قفص / {{ number_format($custodian['issued_weight_kg'], 3) }} كجم</td>
                                    <td class="px-4 py-4 text-slate-600">{{ $custodian['returned_crates'] }} قفص / {{ number_format($custodian['returned_weight_kg'], 3) }} كجم</td>
                                    <td class="px-4 py-4 font-black {{ $custodian['outstanding_crates'] > 0 ? 'text-red-700' : 'text-emerald-700' }}">
                                        {{ $custodian['outstanding_crates'] }} قفص / {{ number_format($custodian['outstanding_weight_kg'], 3) }} كجم
                                    </td>
                                    <td class="px-4 py-4 text-slate-600">{{ count($custodian['entries']) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-4 py-10 text-center text-slate-500">لا توجد عُهد مسجلة في هذا البراد.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if (count($summary['custodians']) > 0)
                    <div class="m-4 rounded-2xl bg-emerald-50 px-4 py-4 text-sm leading-6 text-emerald-900 sm:m-6">
                        إجمالي العُهدة في {{ $summary['cold_store'] }}:
                        <strong>{{ $summary['total']['issued_crates'] }}</strong> مصروف،
                        <strong>{{ $summary['total']['returned_crates'] }}</strong> مرتجع،
                        <strong>{{ $summary['total']['outstanding_crates'] }}</strong> متبقٍ.
                    </div>
                @endif
            </section>
        @empty
            <div class="rounded-3xl border border-dashed bg-white px-6 py-12 text-center text-sm text-slate-500 shadow-sm">لا توجد مخازن مفتوحة حاليًا.</div>
        @endforelse

        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-4 py-5 sm:px-6">
                <h2 class="text-xl font-black text-slate-900">إرجاع عُهدة</h2>
                <p class="mt-1 text-sm leading-6 text-slate-500">الرجوع يتم على نفس الشخص ونفس البراد والحمولة التي خرجت منه، مع اختيار مكان الإرجاع.</p>
            </div>
            <div class="p-4 sm:p-6">
                @forelse ($openHoldings as $holding)
                    <form method="POST" action="{{ route('warehouse.custody.return', [$holding['cold_store']->id, $holding['load']->id, $holding['custodian']->id]) }}" class="mb-4 rounded-2xl border border-slate-200 bg-slate-50/70 p-4 last:mb-0 sm:p-5">
                        @csrf
                        <div class="mb-4">
                            <div class="text-base font-black text-slate-900">{{ $holding['custodian']->name }}</div>
                            <div class="mt-1 text-xs text-slate-500">{{ $holding['cold_store']->name }} — {{ $holding['load']->load_number }}</div>
                            <div class="mt-2 inline-flex rounded-xl bg-red-50 px-3 py-2 text-xs font-bold text-red-700">متاح للإرجاع: {{ $holding['crates_count'] }} قفص / {{ number_format($holding['weight_kg'], 3) }} كجم</div>
                        </div>
                        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                            <input name="crates_count" type="number" min="1" max="{{ $holding['crates_count'] }}" required placeholder="عدد الأقفاص" inputmode="numeric" class="h-12 rounded-xl border border-slate-200 bg-white px-4 text-sm outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10">
                            <input name="weight_kg" type="number" step="0.001" min="0.001" max="{{ $holding['weight_kg'] }}" placeholder="الوزن كجم" inputmode="decimal" class="h-12 rounded-xl border border-slate-200 bg-white px-4 text-sm outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10">
                            <select name="destination" required class="h-12 rounded-xl border border-slate-200 bg-white px-4 text-sm outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10">
                                <option value="vehicle">إرجاع للسيارة</option>
                                <option value="cold_store">إرجاع للبراد</option>
                            </select>
                            <button class="min-h-12 rounded-xl bg-emerald-700 px-4 py-3 text-sm font-black text-white transition hover:bg-emerald-800">تسجيل الإرجاع</button>
                            <input type="hidden" name="notes" value="">
                        </div>
                    </form>
                @empty
                    <div class="rounded-2xl bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">لا توجد عُهد مفتوحة تحتاج إلى إرجاع.</div>
                @endforelse
            </div>
        </section>

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
</x-layouts.app>
