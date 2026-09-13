<x-layouts.app title="استلام شحنة">
    <main dir="rtl" class="mx-auto max-w-5xl space-y-6 px-6 py-8">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <a href="{{ route('dashboard') }}" class="text-sm text-gray-500 hover:text-gray-700">← لوحة التحكم</a>
                <h1 class="mt-2 text-3xl font-bold">استلام شحنة رمان</h1>
                <p class="mt-2 text-sm text-gray-500">تسجيل الشحنة والبيانات الأساسية قبل نقل الأقفاص إلى المخزن المبرد.</p>
            </div>
        </div>

        @if ($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                <p class="font-semibold">راجع البيانات التالية:</p>
                <ul class="mt-2 list-disc space-y-1 pr-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('receiving.loads.store') }}" class="rounded-2xl border bg-white p-6 shadow-sm">
            @csrf
            <div class="grid gap-5 md:grid-cols-2">
                <div>
                    <label for="load_number" class="block text-sm font-medium text-gray-700">رقم الشحنة *</label>
                    <input id="load_number" name="load_number" value="{{ old('load_number') }}" required
                           class="mt-2 w-full rounded-xl border-gray-300 px-4 py-3 shadow-sm focus:border-gray-500 focus:ring-gray-500">
                </div>

                <div>
                    <label for="received_at" class="block text-sm font-medium text-gray-700">تاريخ ووقت الاستلام</label>
                    <input id="received_at" type="datetime-local" name="received_at" value="{{ old('received_at', now()->format('Y-m-d\\TH:i')) }}"
                           class="mt-2 w-full rounded-xl border-gray-300 px-4 py-3 shadow-sm focus:border-gray-500 focus:ring-gray-500">
                </div>

                <div>
                    <label for="supplier_id" class="block text-sm font-medium text-gray-700">المورد</label>
                    <select id="supplier_id" name="supplier_id"
                            class="mt-2 w-full rounded-xl border-gray-300 px-4 py-3 shadow-sm focus:border-gray-500 focus:ring-gray-500">
                        <option value="">بدون تحديد</option>
                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" @selected(old('supplier_id') == $supplier->id)>{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="vehicle_id" class="block text-sm font-medium text-gray-700">السيارة</label>
                    <select id="vehicle_id" name="vehicle_id"
                            class="mt-2 w-full rounded-xl border-gray-300 px-4 py-3 shadow-sm focus:border-gray-500 focus:ring-gray-500">
                        <option value="">بدون تحديد</option>
                        @foreach ($vehicles as $vehicle)
                            <option value="{{ $vehicle->id }}" @selected(old('vehicle_id') == $vehicle->id)>
                                {{ $vehicle->plate_number }}{{ $vehicle->driver_name ? ' — '.$vehicle->driver_name : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="crates_count" class="block text-sm font-medium text-gray-700">عدد الأقفاص *</label>
                    <input id="crates_count" type="number" min="1" name="crates_count" value="{{ old('crates_count') }}" required
                           class="mt-2 w-full rounded-xl border-gray-300 px-4 py-3 shadow-sm focus:border-gray-500 focus:ring-gray-500">
                </div>

                <div>
                    <label for="weight_kg" class="block text-sm font-medium text-gray-700">الوزن بالكيلو *</label>
                    <input id="weight_kg" type="number" min="0.001" step="0.001" name="weight_kg" value="{{ old('weight_kg') }}" required
                           class="mt-2 w-full rounded-xl border-gray-300 px-4 py-3 shadow-sm focus:border-gray-500 focus:ring-gray-500">
                </div>

                <div class="md:col-span-2">
                    <label for="notes" class="block text-sm font-medium text-gray-700">ملاحظات</label>
                    <textarea id="notes" name="notes" rows="4"
                              class="mt-2 w-full rounded-xl border-gray-300 px-4 py-3 shadow-sm focus:border-gray-500 focus:ring-gray-500">{{ old('notes') }}</textarea>
                </div>
            </div>

            <div class="mt-6 flex items-center justify-end gap-3 border-t pt-6">
                <a href="{{ route('dashboard') }}" class="rounded-xl border px-5 py-3 text-sm font-semibold hover:bg-gray-50">إلغاء</a>
                <button type="submit" class="rounded-xl bg-gray-900 px-5 py-3 text-sm font-semibold text-white hover:bg-gray-800">حفظ الشحنة</button>
            </div>
        </form>
    </main>
</x-layouts.app>
