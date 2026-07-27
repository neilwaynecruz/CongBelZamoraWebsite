@php
    /** @var list<array{barangay: string, status: string, total: int}> $rows */
@endphp

<x-filament-widgets::widget>
    <div class="fi-wi-widget p-6 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl shadow-sm space-y-4">
        <div>
            <h2 class="text-lg font-bold text-gray-900 dark:text-white">Projects by barangay / status</h2>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                Aggregate project counts for operational reporting. No sensitive citizen data is shown.
            </p>
        </div>

        <div class="overflow-x-auto border border-gray-100 dark:border-gray-800 rounded-lg">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-800">
                        <th class="px-4 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Barangay</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($rows as $row)
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ $row['barangay'] }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $row['status'] }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $row['total'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-6 text-sm text-gray-500 dark:text-gray-400 text-center">
                                No project records available.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-filament-widgets::widget>
