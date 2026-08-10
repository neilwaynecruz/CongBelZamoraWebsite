@php
    /** @var list<array{barangay: string, status: string, total: int}> $rows */
@endphp

<x-filament-widgets::widget>
    <div class="fi-wi-widget space-y-4 p-6">
        <div>
            <h2 class="text-lg font-bold text-gray-900 dark:text-white">Projects by barangay / status</h2>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                Aggregate project counts for operational reporting. No sensitive citizen data is shown.
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="district-dashboard-table text-left">
                <thead>
                    <tr>
                        <th>Barangay</th>
                        <th>Status</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        <tr>
                            <td class="text-sm text-gray-900 dark:text-white">{{ $row['barangay'] }}</td>
                            <td class="text-sm text-gray-700 dark:text-gray-300">{{ $row['status'] }}</td>
                            <td class="text-sm text-gray-700 dark:text-gray-300">{{ $row['total'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-sm text-gray-500 dark:text-gray-400">
                                No project records available.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-filament-widgets::widget>
