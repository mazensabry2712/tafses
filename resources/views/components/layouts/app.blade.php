<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Tafses ERP' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gray-100 text-gray-900">
    <header class="border-b bg-white">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4">
            <a href="{{ route('dashboard') }}" class="font-semibold">Tafses ERP</a>
            @auth
                <div class="flex items-center gap-4 text-sm">
                    <span>{{ auth()->user()->name }} ({{ auth()->user()->role }})</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="rounded border px-3 py-1.5 hover:bg-gray-50">Logout</button>
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
