<x-layouts.app title="إدارة المستخدمين">
    <main dir="rtl" class="mx-auto max-w-7xl space-y-6 px-6 py-8">
        <section>
            <h1 class="text-2xl font-bold">إدارة المستخدمين</h1>
            <p class="mt-1 text-sm text-gray-600">إنشاء المستخدمين وإدارة الأدوار وحالة الدخول.</p>
        </section>

        @if ($errors->any())
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                <ul class="list-disc space-y-1 pr-5">
                    @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        <section class="rounded-2xl border bg-white p-6 shadow-sm">
            <h2 class="font-semibold">مستخدم جديد</h2>
            <form method="POST" action="{{ route('management.store') }}" class="mt-4 grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                @csrf
                <input name="name" required maxlength="150" placeholder="الاسم" class="rounded-lg border-gray-300">
                <input name="email" type="email" required maxlength="255" placeholder="البريد الإلكتروني" class="rounded-lg border-gray-300">
                <select name="role" required class="rounded-lg border-gray-300">
                    @foreach ($roles as $role)<option value="{{ $role }}">{{ $role }}</option>@endforeach
                </select>
                <input name="password" type="password" required minlength="8" placeholder="كلمة المرور" class="rounded-lg border-gray-300">
                <div class="lg:col-span-4"><button class="rounded-lg bg-gray-900 px-5 py-2.5 text-sm font-semibold text-white hover:bg-gray-700">إنشاء المستخدم</button></div>
            </form>
        </section>

        <section class="overflow-hidden rounded-2xl border bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full text-right text-sm">
                    <thead class="bg-gray-50 text-gray-500"><tr><th class="px-4 py-3">المستخدم</th><th class="px-4 py-3">البريد</th><th class="px-4 py-3">الدور</th><th class="px-4 py-3">الحالة</th><th class="px-4 py-3">إجراء</th></tr></thead>
                    <tbody>
                    @foreach ($users as $user)
                        <tr class="border-t"><td class="px-4 py-3 font-semibold">{{ $user->name }}</td><td class="px-4 py-3">{{ $user->email }}</td><td class="px-4 py-3">{{ $user->role }}</td><td class="px-4 py-3">{{ $user->is_active ? 'نشط' : 'معطل' }}</td><td class="px-4 py-3">@if (!auth()->user()->is($user))<form method="POST" action="{{ route('management.toggle', $user) }}">@csrf<button class="rounded-lg border px-3 py-1.5 text-xs">{{ $user->is_active ? 'تعطيل' : 'تفعيل' }}</button></form>@else<span class="text-xs text-gray-400">المستخدم الحالي</span>@endif</td></tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</x-layouts.app>
