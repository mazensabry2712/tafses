<x-layouts.app title="سجل التدقيق">
    <main dir="rtl" class="mx-auto max-w-7xl space-y-6 px-6 py-8">
        <section>
            <h1 class="text-2xl font-bold">سجل التدقيق</h1>
            <p class="mt-1 text-sm text-gray-600">كل نشاط مسجل للمستخدمين مع المسار والطريقة وحالة الطلب.</p>
        </section>
        <section class="overflow-hidden rounded-2xl border bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full text-right text-sm">
                    <thead class="bg-gray-50"><tr class="border-b text-gray-500"><th class="px-4 py-3 font-medium">الوقت</th><th class="px-4 py-3 font-medium">المستخدم</th><th class="px-4 py-3 font-medium">الدور</th><th class="px-4 py-3 font-medium">الإجراء</th><th class="px-4 py-3 font-medium">المسار</th><th class="px-4 py-3 font-medium">الطريقة</th><th class="px-4 py-3 font-medium">الحالة</th></tr></thead>
                    <tbody>
                    @forelse ($logs as $log)
                        <tr class="border-b last:border-0 hover:bg-gray-50">
                            <td class="whitespace-nowrap px-4 py-3">{{ optional($log->created_at)->format('Y-m-d H:i:s') }}</td>
                            <td class="px-4 py-3 font-semibold">{{ $log->user?->name ?? 'زائر' }}</td>
                            <td class="px-4 py-3">{{ $log->role ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $log->action }}</td>
                            <td class="px-4 py-3 font-mono text-xs">{{ $log->route ?? $log->path ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $log->method }}</td>
                            <td class="px-4 py-3">{{ $log->status_code ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-10 text-center text-gray-500">لا توجد سجلات تدقيق حتى الآن.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </section>
        <div>{{ $logs->links() }}</div>
    </main>
</x-layouts.app>
