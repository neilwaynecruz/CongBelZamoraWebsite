@php
    /** @var array<int, array{type: string, title: string, status: string, date: mixed, severity: string, url: string}> $items */
@endphp

<x-filament-widgets::widget>
    <div class="district-attention-card fi-wi-widget space-y-4 p-6">
        <div>
            <h2 class="flex items-center gap-2 text-lg font-bold text-gray-900 dark:text-white">
                <svg class="h-5 w-5 text-[#d62828]" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                </svg>
                <span>Needs immediate attention</span>
            </h2>
            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                The following tasks are pending approval, require source validation, or have passed their scheduled review dates.
            </p>
        </div>

        @if(count($items) > 0)
            <div class="overflow-x-auto">
                <table class="district-dashboard-table text-left">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Details</th>
                            <th>Status</th>
                            <th>Target date</th>
                            <th class="text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $item)
                            <tr>
                                <!-- Type Badge -->
                                <td class="whitespace-nowrap">
                                    @php
                                        $typeColor = match($item['type']) {
                                            'Approval' => 'bg-red-50 text-red-700 border-red-200 dark:bg-red-950/20 dark:text-red-400 dark:border-red-900/50',
                                            'Source' => 'bg-yellow-50 text-yellow-700 border-yellow-200 dark:bg-yellow-950/20 dark:text-yellow-400 dark:border-yellow-900/50',
                                            'Inquiry' => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/20 dark:text-blue-400 dark:border-blue-900/50',
                                            default => 'bg-orange-50 text-orange-700 border-orange-200 dark:bg-orange-950/20 dark:text-orange-400 dark:border-orange-900/50',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium border {{ $typeColor }}">
                                        {{ $item['type'] }}
                                    </span>
                                </td>

                                <!-- Title/Description -->
                                <td>
                                    <div class="text-sm font-medium text-gray-900 dark:text-white line-clamp-1">
                                        {{ $item['title'] }}
                                    </div>
                                </td>

                                <!-- Status Label -->
                                <td class="whitespace-nowrap">
                                    <div class="flex items-center gap-1.5">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $item['severity'] === 'danger' ? 'bg-[#d62828]' : 'bg-[#c9a227]' }}"></span>
                                        <span class="text-xs font-medium text-gray-700 dark:text-gray-300">{{ $item['status'] }}</span>
                                    </div>
                                </td>

                                <!-- Date -->
                                <td class="whitespace-nowrap text-xs text-gray-500 dark:text-gray-400">
                                    @if($item['date'] instanceof \Carbon\CarbonInterface)
                                        {{ $item['date']->timezone('Asia/Manila')->format('M j, Y g:i A') }}
                                    @else
                                        {{ $item['date'] }}
                                    @endif
                                </td>

                                <!-- Action Link -->
                                <td class="whitespace-nowrap text-right text-sm">
                                    <a href="{{ $item['url'] }}"
                                       class="inline-flex items-center justify-center px-3 py-1.5 border border-transparent text-xs font-semibold rounded-md text-white bg-[#0b1f3a] hover:bg-[#1746a2] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0b1f3a] transition-all duration-150 hover:scale-[1.02]">
                                        Open
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="district-publish-readiness flex flex-col items-center justify-center py-8 text-center">
                <div class="w-10 h-10 rounded-full bg-green-50 dark:bg-green-950/20 border border-green-200 dark:border-green-900 flex items-center justify-center text-[#16733c] mb-3">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                </div>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">All Clear</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 max-w-[280px]">
                    No urgent reviews or pending approvals found. All systems are running smoothly.
                </p>
            </div>
        @endif
    </div>
</x-filament-widgets::widget>
