<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tafses Audit Log</title>
    <style>
        body { font-family: sans-serif; margin: 2rem; background: #f7f7f7; }
        h1 { margin-bottom: .25rem; }
        .muted { color: #666; }
        table { width: 100%; border-collapse: collapse; background: #fff; margin-top: 1.5rem; }
        th, td { padding: .75rem; border-bottom: 1px solid #ddd; text-align: left; }
        th { background: #f0f0f0; }
        nav { margin-top: 1rem; }
    </style>
</head>
<body>
    <h1>Audit Log</h1>
    <p class="muted">Authenticated activity recorded by user, role, route, method, and status.</p>

    <table>
        <thead>
            <tr>
                <th>Time</th>
                <th>User</th>
                <th>Role</th>
                <th>Action</th>
                <th>Route</th>
                <th>Method</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($logs as $log)
                <tr>
                    <td>{{ $log->created_at }}</td>
                    <td>{{ $log->user?->name ?? 'Guest' }}</td>
                    <td>{{ $log->role ?? '-' }}</td>
                    <td>{{ $log->action }}</td>
                    <td>{{ $log->route ?? $log->path }}</td>
                    <td>{{ $log->method }}</td>
                    <td>{{ $log->status_code ?? '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="7">No audit entries yet.</td></tr>
            @endforelse
        </tbody>
    </table>

    {{ $logs->links() }}
</body>
</html>
