@php
    /** @var list<array{model: string, label: string, overdue: int, tracked: int, rate: float}> $rows */
@endphp

<x-filament-widgets::widget>
    <div class="fi-wi-widget space-y-4 p-6">
        <div>
            <h2 class="text-lg font-bold text-gray-900 dark:text-white">Stale content rates</h2>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                Overdue review counts versus tracked records with a review due date.
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="district-dashboard-table text-left">
                <thead>
                    <tr>
                        <th>Content type</th>
                        <th>Overdue</th>
                        <th>Tracked</th>
                        <th>Stale rate</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        <tr>
                            <td class="text-sm text-gray-900 dark:text-white">{{ $row['label'] }}</td>
                            <td class="text-sm text-gray-700 dark:text-gray-300">{{ $row['overdue'] }}</td>
                            <td class="text-sm text-gray-700 dark:text-gray-300">{{ $row['tracked'] }}</td>
                            <td class="text-sm text-gray-700 dark:text-gray-300">{{ number_format($row['rate'], 1) }}%</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-sm text-gray-500 dark:text-gray-400">
                                No tracked review dates available.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-filament-widgets::widget>
