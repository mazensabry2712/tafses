<x-layouts.app>
    <div class="p-6 space-y-6">
        <div>
            <h1 class="text-2xl font-semibold">Operational Report</h1>
            <p class="text-sm text-gray-600">{{ $from }} to {{ $to }}</p>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <div class="rounded-lg border bg-white p-4">
                <div class="text-sm text-gray-500">Received Loads</div>
                <div class="mt-1 text-2xl font-semibold">{{ $summary['receiving']['loads_count'] }}</div>
            </div>
            <div class="rounded-lg border bg-white p-4">
                <div class="text-sm text-gray-500">Sales</div>
                <div class="mt-1 text-2xl font-semibold">{{ number_format($summary['sales']['total_amount'], 2) }}</div>
            </div>
            <div class="rounded-lg border bg-white p-4">
                <div class="text-sm text-gray-500">Processing Output (kg)</div>
                <div class="mt-1 text-2xl font-semibold">{{ number_format($summary['processing']['output_weight_kg'], 3) }}</div>
            </div>
        </div>

        <div class="rounded-lg border bg-white p-4">
            <h2 class="mb-3 font-semibold">Finished Product Stock</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="border-b">
                        <tr>
                            <th class="px-3 py-2 text-left">Product</th>
                            <th class="px-3 py-2 text-left">Code</th>
                            <th class="px-3 py-2 text-left">Quantity</th>
                            <th class="px-3 py-2 text-left">Unit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($summary['finished_product_stock'] as $product)
                            <tr class="border-b last:border-0">
                                <td class="px-3 py-2">{{ $product['name'] }}</td>
                                <td class="px-3 py-2">{{ $product['code'] }}</td>
                                <td class="px-3 py-2">{{ number_format($product['quantity'], 3) }}</td>
                                <td class="px-3 py-2">{{ $product['unit'] }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-3 py-4 text-center text-gray-500">No finished product stock.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.app>
