<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Tafses ERP' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            500: '#22c55e',
                            600: '#16a34a',
                            700: '#15803d',
                            800: '#166534',
                            900: '#14532d'
                        }
                    },
                    boxShadow: {
                        soft: '0 10px 30px rgba(15, 23, 42, 0.07)'
                    }
                }
            }
        };
    </script>
</head>
<body class="min-h-screen bg-slate-50 text-slate-900 antialiased">
@auth
    <div id="mobile-overlay" class="fixed inset-0 z-40 hidden bg-slate-950/50 lg:hidden" onclick="toggleSidebar(false)"></div>

    <aside id="sidebar" class="fixed inset-y-0 right-0 z-50 flex w-72 translate-x-full flex-col border-l border-slate-800 bg-slate-950 text-white shadow-2xl transition-transform duration-200 lg:translate-x-0">
        <div class="flex h-20 shrink-0 items-center justify-between border-b border-white/10 px-5">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3" onclick="toggleSidebar(false)">
                <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-brand-600 text-xl shadow-lg shadow-brand-600/20">🍎</span>
                <span>
                    <span class="block text-lg font-bold leading-tight">Tafses</span>
                    <span class="block text-xs text-slate-400">قطاف الرمان والتصنيع</span>
                </span>
            </a>
            <button type="button" class="rounded-lg p-2 text-slate-400 hover:bg-white/10 lg:hidden" onclick="toggleSidebar(false)" aria-label="إغلاق القائمة">✕</button>
        </div>

        <div class="border-b border-white/10 px-5 py-4">
            <div class="rounded-2xl bg-white/5 p-3">
                <p class="truncate text-sm font-semibold">{{ auth()->user()->name }}</p>
                <p class="mt-1 text-xs text-slate-400">{{ auth()->user()->role }}</p>
            </div>
        </div>

        <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4 text-sm">
            <p class="mb-2 px-3 text-[11px] font-bold uppercase tracking-wider text-slate-500">الرئيسية</p>
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 rounded-xl px-3 py-3 font-medium transition {{ request()->routeIs('dashboard') ? 'bg-brand-600 text-white' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}" onclick="toggleSidebar(false)"><span>⌂</span> لوحة التحكم</a>

            <p class="mb-2 mt-5 px-3 text-[11px] font-bold uppercase tracking-wider text-slate-500">التشغيل</p>
            @if(auth()->user()->canPermission(\App\Support\Permissions::RECEIVE_LOADS))
                <a href="{{ route('receiving.index') }}" class="flex items-center gap-3 rounded-xl px-3 py-3 font-medium transition {{ request()->routeIs('receiving.*') ? 'bg-white/10 text-white' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}" onclick="toggleSidebar(false)"><span>📥</span> الاستلام والشحنات</a>
            @endif
            @if(auth()->user()->canPermission(\App\Support\Permissions::MANAGE_COLD_STORES))
                <a href="{{ route('warehouse') }}" class="flex items-center gap-3 rounded-xl px-3 py-3 font-medium transition {{ request()->routeIs('warehouse') && !request()->routeIs('warehouse.custody') ? 'bg-white/10 text-white' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}" onclick="toggleSidebar(false)"><span>🏭</span> المخازن</a>
            @endif
            @if(auth()->user()->canPermission(\App\Support\Permissions::MANAGE_CUSTODY))
                <a href="{{ route('warehouse.custody') }}" class="flex items-center gap-3 rounded-xl px-3 py-3 font-medium transition {{ request()->routeIs('warehouse.custody') ? 'bg-white/10 text-white' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}" onclick="toggleSidebar(false)"><span>🤝</span> العُهد</a>
            @endif
            @if(auth()->user()->canPermission(\App\Support\Permissions::PROCESS_POMEGRANATES))
                <a href="{{ route('processing.index') }}" class="flex items-center gap-3 rounded-xl px-3 py-3 font-medium transition {{ request()->routeIs('processing.*') ? 'bg-white/10 text-white' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}" onclick="toggleSidebar(false)"><span>⚙️</span> التصنيع</a>
            @endif

            <p class="mb-2 mt-5 px-3 text-[11px] font-bold uppercase tracking-wider text-slate-500">الحسابات</p>
            @if(auth()->user()->canPermission(\App\Support\Permissions::MANAGE_SALES))
                <a href="{{ route('sales.index') }}" class="flex items-center gap-3 rounded-xl px-3 py-3 font-medium transition {{ request()->routeIs('sales.*') ? 'bg-white/10 text-white' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}" onclick="toggleSidebar(false)"><span>🧾</span> المبيعات</a>
            @endif
            @if(auth()->user()->canPermission(\App\Support\Permissions::MANAGE_SUPPLIERS))
                <a href="{{ route('suppliers.index') }}" class="flex items-center gap-3 rounded-xl px-3 py-3 font-medium transition {{ request()->routeIs('suppliers.*') ? 'bg-white/10 text-white' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}" onclick="toggleSidebar(false)"><span>🚚</span> الموردون والمشتريات</a>
            @endif
            @if(auth()->user()->canPermission(\App\Support\Permissions::MANAGE_PAYMENTS))
                <a href="{{ route('accounting') }}" class="flex items-center gap-3 rounded-xl px-3 py-3 font-medium transition {{ request()->routeIs('accounting') ? 'bg-white/10 text-white' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}" onclick="toggleSidebar(false)"><span>💰</span> الحسابات</a>
            @endif
            @if(auth()->user()->canPermission(\App\Support\Permissions::VIEW_REPORTS))
                <a href="{{ route('reports.index') }}" class="flex items-center gap-3 rounded-xl px-3 py-3 font-medium transition {{ request()->routeIs('reports.*') ? 'bg-white/10 text-white' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}" onclick="toggleSidebar(false)"><span>📊</span> التقارير</a>
            @endif

            <p class="mb-2 mt-5 px-3 text-[11px] font-bold uppercase tracking-wider text-slate-500">الإدارة</p>
            @if(auth()->user()->canPermission(\App\Support\Permissions::MANAGE_MASTER_DATA))
                <a href="{{ route('master-data') }}" class="flex items-center gap-3 rounded-xl px-3 py-3 font-medium transition {{ request()->routeIs('master-data*') ? 'bg-white/10 text-white' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}" onclick="toggleSidebar(false)"><span>🗂️</span> البيانات الأساسية</a>
            @endif
            @if(auth()->user()->canPermission(\App\Support\Permissions::MANAGE_USERS))
                <a href="{{ route('management') }}" class="flex items-center gap-3 rounded-xl px-3 py-3 font-medium transition {{ request()->routeIs('management*') ? 'bg-white/10 text-white' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}" onclick="toggleSidebar(false)"><span>👥</span> المستخدمون</a>
            @endif
            @if(auth()->user()->canPermission(\App\Support\Permissions::VIEW_AUDIT))
                <a href="{{ route('audit.index') }}" class="flex items-center gap-3 rounded-xl px-3 py-3 font-medium transition {{ request()->routeIs('audit.*') ? 'bg-white/10 text-white' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}" onclick="toggleSidebar(false)"><span>🔎</span> سجل التدقيق</a>
            @endif
        </nav>

        <div class="shrink-0 border-t border-white/10 p-3">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="flex w-full items-center justify-center gap-2 rounded-xl border border-white/10 px-3 py-3 text-sm font-semibold text-slate-300 transition hover:bg-white/10 hover:text-white"><span>↪</span> تسجيل الخروج</button>
            </form>
        </div>
    </aside>

    <div class="min-h-screen lg:pr-72">
        <header class="sticky top-0 z-30 border-b border-slate-200 bg-white/90 backdrop-blur">
            <div class="flex h-16 items-center justify-between gap-4 px-4 sm:px-6">
                <div class="flex min-w-0 items-center gap-3">
                    <button type="button" class="rounded-xl border border-slate-200 p-2.5 text-slate-700 hover:bg-slate-50 lg:hidden" onclick="toggleSidebar(true)" aria-label="فتح القائمة">☰</button>
                    <div class="min-w-0">
                        <p class="truncate text-xs text-slate-500 sm:text-sm">نظام قطاف الرمان والتصنيع</p>
                        <p class="hidden truncate text-sm font-bold text-slate-900 sm:block">{{ $title ?? 'لوحة التحكم' }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 sm:gap-3">
                    <span class="hidden rounded-full bg-brand-50 px-3 py-1.5 text-xs font-semibold text-brand-700 sm:inline-flex">{{ auth()->user()->role }}</span>
                    <span class="hidden max-w-32 truncate text-sm font-semibold text-slate-700 md:inline">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}" class="hidden sm:block">
                        @csrf
                        <button class="rounded-xl border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">خروج</button>
                    </form>
                </div>
            </div>
        </header>

        @if (session('success'))
            <div class="px-4 pt-4 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-7xl rounded-2xl border border-brand-200 bg-brand-50 px-4 py-3 text-sm font-medium text-brand-800 shadow-sm">{{ session('success') }}</div>
            </div>
        @endif
        @if ($errors->any())
            <div class="px-4 pt-4 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-7xl rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 shadow-sm">
                    <p class="font-bold">تعذر تنفيذ العملية</p>
                    <div class="mt-1 space-y-1">@foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>
                </div>
            </div>
        @endif

        {{ $slot }}
    </div>
@else
    {{ $slot }}
@endauth

<script>
    function toggleSidebar(open) {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('mobile-overlay');
        if (!sidebar || !overlay) return;
        sidebar.classList.toggle('translate-x-full', !open);
        sidebar.classList.toggle('translate-x-0', open);
        overlay.classList.toggle('hidden', !open);
        document.body.classList.toggle('overflow-hidden', open);
    }
</script>
</body>
</html>
