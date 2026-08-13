@php
    /** @var string $userName */
    /** @var \Illuminate\Support\Carbon $currentTime */
    /** @var array<int, array{label: string, url: string, icon: string, color: string}> $actions */
    /** @var bool $canRunHealthCheck */
@endphp

<x-filament-widgets::widget>
    <div class="district-command-card fi-wi-widget relative p-6 sm:p-7">
        <div class="absolute inset-x-0 top-0 h-1" style="background: linear-gradient(90deg, #0b1f3a 0%, #1746a2 42%, #c9a227 72%, #d62828 100%);"></div>

        <div class="district-command-shell mt-1">
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-3">
                    <span class="district-login-seal-row">
                        <img
                            src="{{ asset('assets/images/brand/House_of_Representative_Logo_Seal.svg') }}"
                            alt=""
                            class="district-login-seal h-10! w-10!"
                        >
                        <img
                            src="{{ asset('assets/images/brand/Logo_of_San_Juan_Seal.svg') }}"
                            alt=""
                            class="district-login-seal h-10! w-10!"
                        >
                    </span>
                    <span class="district-command-kicker">Bel Zamora District Office</span>
                </div>

                <h1 class="district-command-title mt-4">
                    Operations Command Center
                </h1>

                <p class="district-command-summary">
                    Welcome, <span class="font-semibold text-gray-900 dark:text-white">{{ $userName }}</span>. This dashboard provides live status summaries, work queues, and quick actions for managing official programs, news, and inquiries.
                </p>

                <div class="district-governance-warning mt-4 border border-brand-gold/40 bg-[#fff8ea] text-[#4a3600]" role="status">
                    <x-filament::icon icon="heroicon-m-exclamation-triangle" class="text-[#8a6500]" />
                    <div>
                        <strong>Emergency admin only.</strong>
                        <span>Use the District Office Command System at <a href="{{ route('admin-console.dashboard') }}" class="font-semibold underline">/admin-console</a> for daily staff work. This older admin area stays available only for Super Administrators when recovery, approved exports, or transition checks are needed.</span>
                    </div>
                </div>

                <div class="district-command-meta">
                    <span class="district-trust-badge bg-[#fff8ea] text-[#7a5b00]">
                        <svg class="h-4 w-4 text-brand-gold" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Manila Time: <strong>{{ $currentTime->format('l, F j, Y - h:i A') }}</strong></span>
                    </span>
                    <span class="district-trust-badge bg-[#f0fdf4] text-[#14532d]">
                        <svg class="h-4 w-4 text-status-open" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m6 2.25a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                            <span>Review rules active</span>
                    </span>
                    <span class="district-trust-badge bg-[#f8fafc] text-[#334155]">
                        <svg class="h-4 w-4 text-brand-royal" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 12h9.75M10.5 18h9.75M3.75 6h.008v.008H3.75V6Zm0 6h.008v.008H3.75V12Zm0 6h.008v.008H3.75V18Z" />
                        </svg>
                        <span>Queues monitored</span>
                    </span>
                </div>
            </div>

            @if(count($actions) > 0 || $canRunHealthCheck)
                <aside class="district-command-panel" aria-label="Admin quick actions">
                    <p class="district-command-panel-title">Priority actions</p>

                    @foreach($actions as $action)
                        <a href="{{ $action['url'] }}" class="district-command-action">
                            <span class="flex min-w-0 items-center gap-2.5">
                                <x-filament::icon :icon="$action['icon']" class="h-4 w-4 shrink-0" />
                                <span class="truncate">{{ $action['label'] }}</span>
                            </span>
                            <svg class="h-4 w-4 shrink-0 text-brand-gold" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                            </svg>
                        </a>
                    @endforeach

                    @if($canRunHealthCheck)
                        <button
                            type="button"
                            wire:click="checkRequiredPublicPages"
                            wire:loading.attr="disabled"
                            wire:target="checkRequiredPublicPages"
                            class="district-command-action text-left disabled:cursor-wait disabled:opacity-70"
                        >
                            <span class="flex min-w-0 items-center gap-2.5">
                                <x-filament::icon icon="heroicon-s-shield-check" class="h-4 w-4 shrink-0" />
                                <span wire:loading.remove wire:target="checkRequiredPublicPages">Run required pages check</span>
                                <span wire:loading wire:target="checkRequiredPublicPages">Checking required pages...</span>
                            </span>
                            <svg class="h-4 w-4 shrink-0 text-brand-gold" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                            </svg>
                        </button>
                    @endif

                    <div class="district-governance-warning mt-1 bg-white/10! text-white!">
                        <x-filament::icon icon="heroicon-m-shield-check" class="text-brand-gold" />
                        <div>
                            <strong class="text-white!">Official operations mode</strong>
                            <span class="text-white/75!">Review urgent work first. Publish only items with sources, review dates, and approval.</span>
                        </div>
                    </div>
                </aside>
            @else
                <aside class="district-command-panel" aria-label="Admin quick actions">
                    <p class="district-command-panel-title">System state</p>
                    <div class="district-governance-warning bg-white/10! text-white!">
                        <x-filament::icon icon="heroicon-m-check-circle" class="text-brand-gold" />
                        <div>
                            <strong class="text-white!">No priority actions available</strong>
                            <span class="text-white/75!">Visible queues and checks depend on the staff role assigned to you.</span>
                        </div>
                    </div>
                </aside>
            @endif
        </div>
    </div>
</x-filament-widgets::widget>
