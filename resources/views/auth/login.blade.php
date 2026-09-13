<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول | Tafses</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-950 text-slate-900">
    <main class="relative flex min-h-screen items-center justify-center overflow-hidden px-4 py-8 sm:px-6">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(34,197,94,0.18),_transparent_35%),radial-gradient(circle_at_bottom_left,_rgba(59,130,246,0.12),_transparent_35%)]"></div>
        <div class="relative grid w-full max-w-5xl overflow-hidden rounded-3xl border border-white/10 bg-white shadow-2xl lg:grid-cols-2">
            <section class="hidden bg-slate-950 p-10 text-white lg:flex lg:flex-col lg:justify-between">
                <div>
                    <div class="flex items-center gap-3">
                        <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-green-600 text-2xl">🍎</span>
                        <div>
                            <p class="text-xl font-bold">Tafses</p>
                            <p class="text-sm text-slate-400">نظام قطاف الرمان والتصنيع</p>
                        </div>
                    </div>
                    <div class="mt-16 max-w-md">
                        <p class="text-sm font-semibold text-green-400">إدارة التشغيل والمخزون والحسابات</p>
                        <h1 class="mt-3 text-4xl font-bold leading-tight">كل دورة العمل في مكان واحد.</h1>
                        <p class="mt-5 leading-8 text-slate-400">تابع الاستلام والمخازن والعُهد والتصنيع والمبيعات والحسابات من خلال نظام واحد واضح.</p>
                    </div>
                </div>
                <p class="text-xs text-slate-500">Tafses ERP · Internal Business System</p>
            </section>

            <section class="p-6 sm:p-10">
                <div class="mx-auto w-full max-w-md">
                    <div class="lg:hidden flex items-center gap-3">
                        <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-green-600 text-xl text-white">🍎</span>
                        <div>
                            <p class="font-bold">Tafses</p>
                            <p class="text-xs text-slate-500">قطاف الرمان والتصنيع</p>
                        </div>
                    </div>

                    <div class="mt-10 lg:mt-0">
                        <p class="text-sm font-semibold text-green-700">مرحبًا بك</p>
                        <h2 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">تسجيل الدخول</h2>
                        <p class="mt-2 text-sm leading-6 text-slate-500">استخدم حسابك للوصول إلى لوحة التحكم والأقسام المسموح بها.</p>
                    </div>

                    @if ($errors->any())
                        <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                            @foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login.store') }}" class="mt-8 space-y-5">
                        @csrf
                        <div>
                            <label for="email" class="mb-2 block text-sm font-semibold text-slate-700">البريد الإلكتروني</label>
                            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email" placeholder="name@example.com" class="block w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3.5 text-sm outline-none transition placeholder:text-slate-400 focus:border-green-500 focus:bg-white focus:ring-4 focus:ring-green-500/10">
                        </div>
                        <div>
                            <label for="password" class="mb-2 block text-sm font-semibold text-slate-700">كلمة المرور</label>
                            <input id="password" type="password" name="password" required autocomplete="current-password" placeholder="••••••••" class="block w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3.5 text-sm outline-none transition placeholder:text-slate-400 focus:border-green-500 focus:bg-white focus:ring-4 focus:ring-green-500/10">
                        </div>
                        <button type="submit" class="flex w-full items-center justify-center rounded-2xl bg-green-600 px-4 py-3.5 text-sm font-bold text-white shadow-lg shadow-green-600/20 transition hover:bg-green-700 active:scale-[0.99]">دخول إلى النظام</button>
                    </form>
                </div>
            </section>
        </div>
    </main>
</body>
</html>
