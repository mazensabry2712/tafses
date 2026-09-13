<x-layouts.app title="سجل التدقيق">
    <main dir="rtl" class="mx-auto max-w-7xl space-y-6 px-4 py-5 sm:px-6 sm:py-7 lg:px-8">
        <section class="overflow-hidden rounded-3xl bg-gradient-to-br from-slate-950 via-slate-900 to-emerald-950 p-5 text-white shadow-xl sm:p-7">
            <div class="inline-flex rounded-full bg-white/10 px-3 py-1 text-xs font-semibold text-emerald-100 ring-1 ring-white/10">الإدارة / التدقيق</div>
            <h1 class="mt-3 text-2xl font-black tracking-tight sm:text-3xl">سجل التدقيق</h1>
            <p class="mt-2 text-sm leading-6 text-slate-200/80 sm:text-base">كل نشاط مسجل للمستخدمين مع المسار والطريقة وحالة الطلب.</p>
        </section>

        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-[1000px] w-full text-right text-sm">
                    <thead class="bg-slate-50 text-slate-500"><tr class="border-b border-slate-100"><th class="px-4 py-3 font-bold sm:px-6">الوقت</th><th class="px-4 py-3 font-bold">المستخدم</th><th class="px-4 py-3 font-bold">الدور</th><th class="px-4 py-3 font-bold">الإجراء</th><th class="px-4 py-3 font-bold">المسار</th><th class="px-4 py-3 font-bold">الطريقة</th><th class="px-4 py-3 font-bold">الحالة</th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                    @forelse ($logs as $log)
                        <tr class="transition hover:bg-slate-50">
                            <td class="whitespace-nowrap px-4 py-4 text-slate-600 sm:px-6">{{ optional($log->created_at)->format('Y-m-d H:i:s') }}</td>
                            <td class="px-4 py-4 font-bold text-slate-900">{{ $log->user?->name ?? 'زائر' }}</td>
                            <td class="px-4 py-4 text-slate-600">{{ $log->role ?? '-' }}</td>
                            <td class="px-4 py-4 text-slate-600">{{ $log->action }}</td>
                            <td class="px-4 py-4 font-mono text-xs text-slate-500">{{ $log->route ?? $log->path ?? '-' }}</td>
                            <td class="px-4 py-4"><span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-600">{{ $log->method }}</span></td>
                            <td class="px-4 py-4"><span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700">{{ $log->status_code ?? '-' }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-10 text-center text-slate-500">لا توجد سجلات تدقيق حتى الآن.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </section>
        <div class="overflow-x-auto">{{ $logs->links() }}</div>
    </main>
</x-layouts.app>
