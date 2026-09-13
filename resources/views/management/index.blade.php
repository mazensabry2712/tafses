<x-layouts.app title="إدارة المستخدمين">
    <main dir="rtl" class="mx-auto max-w-7xl space-y-6 px-4 py-5 sm:px-6 sm:py-7 lg:px-8">
        <section class="overflow-hidden rounded-3xl bg-gradient-to-br from-slate-950 via-emerald-950 to-slate-900 p-5 text-white shadow-xl sm:p-7">
            <div class="inline-flex rounded-full bg-white/10 px-3 py-1 text-xs font-semibold text-emerald-100 ring-1 ring-white/10">الإدارة / المستخدمون</div>
            <h1 class="mt-3 text-2xl font-black tracking-tight sm:text-3xl">إدارة المستخدمين</h1>
            <p class="mt-2 text-sm leading-6 text-slate-200/80 sm:text-base">إنشاء المستخدمين وإدارة الأدوار وحالة الدخول.</p>
        </section>

        @if ($errors->any())
            <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-4 text-sm text-red-800 shadow-sm"><ul class="list-disc space-y-1 pr-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif

        <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <h2 class="text-xl font-black text-slate-900">مستخدم جديد</h2>
            <form method="POST" action="{{ route('management.store') }}" class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                @csrf
                <input name="name" required maxlength="150" placeholder="الاسم" class="h-12 rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm outline-none focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/10">
                <input name="email" type="email" required maxlength="255" placeholder="البريد الإلكتروني" class="h-12 rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm outline-none focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/10">
                <select name="role" required class="h-12 rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm outline-none focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/10">
                    @foreach ($roles as $role)<option value="{{ $role }}">{{ $role }}</option>@endforeach
                </select>
                <input name="password" type="password" required minlength="8" placeholder="كلمة المرور" class="h-12 rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm outline-none focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/10">
                <div class="sm:col-span-2 xl:col-span-4"><button class="min-h-12 w-full rounded-xl bg-emerald-700 px-5 py-3 text-sm font-black text-white transition hover:bg-emerald-800 xl:w-auto">إنشاء المستخدم</button></div>
            </form>
        </section>

        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-4 py-5 sm:px-6"><h2 class="text-xl font-black text-slate-900">المستخدمون الحاليون</h2></div>
            <div class="overflow-x-auto">
                <table class="min-w-[760px] w-full text-right text-sm">
                    <thead class="bg-slate-50 text-slate-500"><tr><th class="px-4 py-3 font-bold sm:px-6">المستخدم</th><th class="px-4 py-3 font-bold">البريد</th><th class="px-4 py-3 font-bold">الدور</th><th class="px-4 py-3 font-bold">الحالة</th><th class="px-4 py-3 font-bold">إجراء</th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                    @foreach ($users as $user)
                        <tr class="transition hover:bg-slate-50">
                            <td class="px-4 py-4 font-bold text-slate-900 sm:px-6">{{ $user->name }}</td>
                            <td class="px-4 py-4 text-slate-600">{{ $user->email }}</td>
                            <td class="px-4 py-4"><span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">{{ $user->role }}</span></td>
                            <td class="px-4 py-4"><span class="rounded-full px-3 py-1 text-xs font-bold {{ $user->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700' }}">{{ $user->is_active ? 'نشط' : 'معطل' }}</span></td>
                            <td class="px-4 py-4">
                                @if (!auth()->user()->is($user))
                                    <form method="POST" action="{{ route('management.toggle', $user) }}">@csrf<button class="rounded-xl border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 transition hover:bg-slate-100">{{ $user->is_active ? 'تعطيل' : 'تفعيل' }}</button></form>
                                @else
                                    <span class="text-xs text-slate-400">المستخدم الحالي</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</x-layouts.app>
