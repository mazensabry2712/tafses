<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Tafses ERP' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gray-100 text-gray-900">
    <header class="border-b bg-white">
        <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-4 px-6 py-4">
            <a href="{{ route('dashboard') }}" class="font-semibold">Tafses ERP</a>
            @auth
                <nav class="flex flex-wrap items-center gap-3 text-sm">
                    <a href="{{ route('dashboard') }}" class="hover:underline">الرئيسية</a>
                    @if(auth()->user()->canPermission(\App\Support\Permissions::MANAGE_MASTER_DATA))
                        <a href="{{ route('master-data') }}" class="hover:underline">البيانات الأساسية</a>
                    @endif
                    @if(auth()->user()->canPermission(\App\Support\Permissions::RECEIVE_LOADS))<a href="{{ route('receiving.index') }}" class="hover:underline">الاستلام</a>@endif
                    @if(auth()->user()->canPermission(\App\Support\Permissions::MANAGE_COLD_STORES))<a href="{{ route('warehouse') }}" class="hover:underline">المخازن</a>@endif
                    @if(auth()->user()->canPermission(\App\Support\Permissions::PROCESS_POMEGRANATES))<a href="{{ route('processing.index') }}" class="hover:underline">التصنيع</a>@endif
                    @if(auth()->user()->canPermission(\App\Support\Permissions::MANAGE_SALES))<a href="{{ route('sales.index') }}" class="hover:underline">المبيعات</a>@endif
                    @if(auth()->user()->canPermission(\App\Support\Permissions::MANAGE_SUPPLIERS))<a href="{{ route('suppliers.index') }}" class="hover:underline">الموردون</a>@endif
                    @if(auth()->user()->canPermission(\App\Support\Permissions::VIEW_REPORTS))<a href="{{ route('reports.index') }}" class="hover:underline">التقارير</a>@endif
                    @if(auth()->user()->canPermission(\App\Support\Permissions::MANAGE_PAYMENTS))<a href="{{ route('accounting') }}" class="hover:underline">الحسابات</a>@endif
                    @if(auth()->user()->canPermission(\App\Support\Permissions::MANAGE_USERS))<a href="{{ route('management') }}" class="hover:underline">المستخدمون</a>@endif
                    @if(auth()->user()->canPermission(\App\Support\Permissions::VIEW_AUDIT))<a href="{{ route('audit.index') }}" class="hover:underline">التدقيق</a>@endif
                </nav>
                <div class="flex items-center gap-4 text-sm">
                    <span>{{ auth()->user()->name }} ({{ auth()->user()->role }})</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="rounded border px-3 py-1.5 hover:bg-gray-50">خروج</button>
                    </form>
                </div>
            @endauth
        </div>
    </header>

    @if (session('success'))
        <div class="mx-auto max-w-7xl px-6 pt-6">
            <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                {{ session('success') }}
            </div>
        </div>
    @endif

    {{ $slot }}
</body>
</html>
