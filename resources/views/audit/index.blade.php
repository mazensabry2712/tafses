<x-layouts.app>
    <div class="p-6 space-y-4">
        <div>
            <h1 class="text-2xl font-semibold">Audit Log</h1>
            <p class="text-sm text-gray-600">Authenticated activity recorded by user, role, route, method, and status.</p>
        </div>

        <div class="overflow-x-auto rounded-lg border bg-white">
            <table class="min-w-full text-sm">
                <thead class="border-b bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left">Time</th>
                        <th class="px-4 py-3 text-left">User</th>
                        <th class="px-4 py-3 text-left">Role</th>
                        <th class="px-4 py-3 text-left">Action</th>
                        <th class="px-4 py-3 text-left">Route</th>
                        <th class="px-4 py-3 text-left">Method</th>
                        <th class="px-4 py-3 text-left">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr class="border-b last:border-0">
                            <td class="px-4 py-3">{{ $log->created_at }}</td>
                            <td class="px-4 py-3">{{ $log->user?->name ?? 'Guest' }}</td>
                            <td class="px-4 py-3">{{ $log->role ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $log->action }}</td>
                            <td class="px-4 py-3">{{ $log->route ?? $log->path }}</td>
                            <td class="px-4 py-3">{{ $log->method }}</td>
                            <td class="px-4 py-3">{{ $log->status_code ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-6 text-center text-gray-500">No audit entries yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $logs->links() }}
    </div>
</x-layouts.app>
