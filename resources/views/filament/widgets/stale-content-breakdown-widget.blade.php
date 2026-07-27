@php
    /** @var list<array{model: string, label: string, overdue: int, tracked: int, rate: float}> $rows */
@endphp

<x-filament-widgets::widget>
    <div class="fi-wi-widget p-6 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl shadow-sm space-y-4">
        <div>
            <h2 class="text-lg font-bold text-gray-900 dark:text-white">Stale content rates</h2>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                Overdue review counts versus tracked records with a review due date.
            </p>
        </div>

        <div class="overflow-x-auto border border-gray-100 dark:border-gray-800 rounded-lg">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-800">
                        <th class="px-4 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Content type</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Overdue</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Tracked</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Stale rate</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($rows as $row)
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ $row['label'] }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $row['overdue'] }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $row['tracked'] }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ number_format($row['rate'], 1) }}%</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-sm text-gray-500 dark:text-gray-400 text-center">
                                No tracked review dates available.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-filament-widgets::widget>
