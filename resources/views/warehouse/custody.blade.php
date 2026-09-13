<x-layouts.app title="إدارة العُهد">
    <main dir="rtl" class="mx-auto max-w-7xl space-y-6 px-6 py-8">
        <div class="flex flex-col justify-between gap-3 md:flex-row md:items-end">
            <div>
                <h1 class="text-2xl font-bold">إدارة العُهد</h1>
                <p class="mt-1 text-sm text-gray-600">متابعة كل شخص، المصروف، المرتجع، والرصيد المفتوح مع سجل الحركات.</p>
            </div>
            <a href="{{ route('warehouse') }}" class="rounded-lg border bg-white px-4 py-2 text-sm hover:bg-gray-50">العودة للمخازن</a>
        </div>

        @forelse ($summaries as $summary)
            <section class="rounded-xl border bg-white p-6 shadow-sm">
                <div class="flex flex-col gap-3 border-b pb-4 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h2 class="text-xl font-semibold">{{ $summary['cold_store'] }}</h2>
                        @if ($summary['crate_standard'])
                            <p class="mt-1 text-xs text-gray-500">
                                معيار القفص: {{ $summary['crate_standard']['gross_weight_kg'] }} كجم إجمالي − {{ $summary['crate_standard']['tare_weight_kg'] }} كجم فارغ = {{ $summary['crate_standard']['net_weight_kg'] }} كجم صافي.
                            </p>
                        @endif
                    </div>
                    <div class="flex gap-3 text-sm">
                        <span class="rounded-lg bg-gray-50 px-3 py-2">مخزون البراد: <strong>{{ $summary['current']['crates_count'] }}</strong> قفص</span>
                        <span class="rounded-lg bg-gray-50 px-3 py-2">الرصيد المفتوح: <strong>{{ $summary['total']['outstanding_crates'] }}</strong> قفص</span>
                    </div>
                </div>

                <div class="mt-5 overflow-x-auto">
                    <table class="w-full min-w-[760px] text-right text-sm">
                        <thead class="border-b bg-gray-50 text-gray-600">
                            <tr>
                                <th class="px-3 py-3">أمين العهدة</th>
                                <th class="px-3 py-3">المصروف</th>
                                <th class="px-3 py-3">المرتجع</th>
                                <th class="px-3 py-3">المتبقي</th>
                                <th class="px-3 py-3">الحركات</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @forelse ($summary['custodians'] as $custodian)
                                <tr>
                                    <td class="px-3 py-3 font-medium">{{ $custodian['name'] }}</td>
                                    <td class="px-3 py-3">{{ $custodian['issued_crates'] }} قفص / {{ number_format($custodian['issued_weight_kg'], 3) }} كجم</td>
                                    <td class="px-3 py-3">{{ $custodian['returned_crates'] }} قفص / {{ number_format($custodian['returned_weight_kg'], 3) }} كجم</td>
                                    <td class="px-3 py-3 font-semibold {{ $custodian['outstanding_crates'] > 0 ? 'text-red-700' : 'text-green-700' }}">
                                        {{ $custodian['outstanding_crates'] }} قفص / {{ number_format($custodian['outstanding_weight_kg'], 3) }} كجم
                                    </td>
                                    <td class="px-3 py-3">{{ count($custodian['entries']) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-3 py-8 text-center text-gray-500">لا توجد عُهد مسجلة في هذا البراد.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if (count($summary['custodians']) > 0)
                    <div class="mt-5 rounded-lg bg-gray-50 p-4 text-sm">
                        إجمالي العُهدة في {{ $summary['cold_store'] }}:
                        <strong>{{ $summary['total']['issued_crates'] }}</strong> مصروف،
                        <strong>{{ $summary['total']['returned_crates'] }}</strong> مرتجع،
                        <strong>{{ $summary['total']['outstanding_crates'] }}</strong> متبقٍ.
                    </div>
                @endif
            </section>
        @empty
            <div class="rounded-xl border bg-white px-6 py-10 text-center text-sm text-gray-500 shadow-sm">لا توجد مخازن مفتوحة حاليًا.</div>
        @endforelse

        <section class="rounded-xl border bg-white p-6 shadow-sm">
            <div class="mb-4">
                <h2 class="text-lg font-semibold">إرجاع عُهدة</h2>
                <p class="mt-1 text-sm text-gray-500">الرجوع يتم على نفس الشخص ونفس البراد والحمولة التي خرجت منه، مع اختيار مكان الإرجاع.</p>
            </div>

            @forelse ($openHoldings as $holding)
                <form method="POST" action="{{ route('warehouse.custody.return', [$holding['cold_store']->id, $holding['load']->id, $holding['custodian']->id]) }}" class="mb-4 grid gap-3 rounded-xl border p-4 md:grid-cols-6">
                    @csrf
                    <div class="md:col-span-2">
                        <div class="font-medium">{{ $holding['custodian']->name }}</div>
                        <div class="text-xs text-gray-500">{{ $holding['cold_store']->name }} — {{ $holding['load']->load_number }}</div>
                        <div class="mt-1 text-xs text-red-700">متاح للإرجاع: {{ $holding['crates_count'] }} قفص / {{ number_format($holding['weight_kg'], 3) }} كجم</div>
                    </div>
                    <input name="crates_count" type="number" min="1" max="{{ $holding['crates_count'] }}" required placeholder="عدد الأقفاص" class="rounded-lg border-gray-300">
                    <input name="weight_kg" type="number" step="0.001" min="0.001" max="{{ $holding['weight_kg'] }}" placeholder="الوزن كجم" class="rounded-lg border-gray-300">
                    <select name="destination" required class="rounded-lg border-gray-300">
                        <option value="vehicle">إرجاع للسيارة</option>
                        <option value="cold_store">إرجاع للبراد</option>
                    </select>
                    <button class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">تسجيل الإرجاع</button>
                    <input type="hidden" name="notes" value="">
                </form>
            @empty
                <div class="rounded-lg bg-gray-50 px-4 py-6 text-center text-sm text-gray-500">لا توجد عُهد مفتوحة تحتاج إلى إرجاع.</div>
            @endforelse
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
