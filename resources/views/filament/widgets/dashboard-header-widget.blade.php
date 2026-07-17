@php
    /** @var string $userName */
    /** @var \Illuminate\Support\Carbon $currentTime */
    /** @var array<int, array{label: string, url: string, icon: string, color: string}> $actions */
    /** @var bool $canRunHealthCheck */
@endphp

<x-filament-widgets::widget>
    <div class="district-command-card fi-wi-widget relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="absolute inset-x-0 top-0 h-1.5" style="background: linear-gradient(90deg, #0b1f3a 0%, #1746a2 42%, #c9a227 72%, #d62828 100%);"></div>

        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6 mt-1">
            <div class="flex-1 space-y-2">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center justify-center w-2.5 h-2.5 rounded-full bg-status-open animate-pulse"></span>
                    <span class="text-xs font-semibold tracking-wider uppercase text-gray-500 dark:text-gray-400">
                        Bel Zamora District Office
                    </span>
                </div>
                <h1 class="text-2xl font-bold tracking-tight text-brand-navy dark:text-white sm:text-3xl">
                    Operations Command Center
                </h1>
                <p class="text-sm text-gray-600 dark:text-gray-300 max-w-2xl leading-relaxed">
                    Welcome, <span class="font-semibold text-gray-900 dark:text-white">{{ $userName }}</span>. This dashboard provides live status summaries, work queues, and quick actions for managing official programs, news, and inquiries.
                </p>
                <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-[#fff8ea] dark:bg-yellow-950/30 border border-[#c9a227]/20 rounded-lg text-xs font-medium text-brand-gold dark:text-yellow-400">
                    <svg class="w-4 h-4 text-[#c9a227]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Manila Time: <strong>{{ $currentTime->format('l, F j, Y - h:i A') }}</strong></span>
                </div>
            </div>

            @if(count($actions) > 0 || $canRunHealthCheck)
                <div class="w-full lg:w-auto min-w-[280px]">
                    <div class="grid grid-cols-2 gap-3">
                        @foreach($actions as $action)
                            @php
                                $btnClass = match($action['color']) {
                                    'gold' => 'border-[#c9a227] text-[#c9a227] hover:bg-[#c9a227] hover:text-white dark:border-[#c9a227]/50 dark:hover:bg-[#c9a227]/20 dark:hover:text-yellow-400',
                                    'blue' => 'border-[#1746a2] text-[#1746a2] hover:bg-[#1746a2] hover:text-white dark:border-[#1746a2]/50 dark:hover:bg-[#1746a2]/20 dark:hover:text-blue-400',
                                    'navy' => 'border-[#0b1f3a] text-[#0b1f3a] hover:bg-[#0b1f3a] hover:text-white dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-white',
                                    'red' => 'border-[#d62828] text-[#d62828] hover:bg-[#d62828] hover:text-white dark:border-[#d62828]/50 dark:hover:bg-[#d62828]/20 dark:hover:text-red-400',
                                    default => 'border-gray-300 text-gray-700 hover:bg-gray-500 hover:text-white dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800',
                                };
                            @endphp
                            <a href="{{ $action['url'] }}"
                               class="district-command-action flex items-center gap-2.5 px-4 py-3 rounded-lg border bg-transparent font-semibold text-sm transition-all duration-200 hover:scale-[1.02] hover:shadow-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#1746a2] {{ $btnClass }}">
                                <x-filament::icon :icon="$action['icon']" class="w-4 h-4 flex-shrink-0" />
                                <span>{{ $action['label'] }}</span>
                            </a>
                        @endforeach

                        @if($canRunHealthCheck)
                            <button
                                type="button"
                                wire:click="checkRequiredPublicPages"
                                wire:loading.attr="disabled"
                                wire:target="checkRequiredPublicPages"
                                class="district-command-action col-span-2 flex items-center justify-center gap-2.5 rounded-lg border border-[#16733c] bg-[#16733c] px-4 py-3 text-sm font-semibold text-white transition-all duration-200 hover:scale-[1.01] hover:bg-[#115a2f] hover:shadow-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#16733c] disabled:cursor-wait disabled:opacity-70"
                            >
                                <x-filament::icon icon="heroicon-s-shield-check" class="w-4 h-4 flex-shrink-0" />
                                <span wire:loading.remove wire:target="checkRequiredPublicPages">Run Required Pages Check</span>
                                <span wire:loading wire:target="checkRequiredPublicPages">Checking required pages...</span>
                            </button>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-filament-widgets::widget>
