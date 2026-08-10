@php
    use Filament\Support\Facades\FilamentView;
    use Filament\View\PanelsRenderHook;
@endphp

<div class="district-admin-login" aria-labelledby="district-admin-login-title">
    <section class="district-login-identity" aria-label="District Office administration identity">
        <div class="district-login-seal-row" aria-hidden="true">
            <img
                src="{{ asset('assets/images/brand/House_of_Representative_Logo_Seal.svg') }}"
                alt=""
                class="district-login-seal"
            >
            <img
                src="{{ asset('assets/images/brand/Logo_of_San_Juan_Seal.svg') }}"
                alt=""
                class="district-login-seal"
            >
        </div>

        <div class="district-login-copy">
            <p class="district-login-kicker">Internal operations console</p>
            <h1 id="district-admin-login-title">District Office Administration</h1>
            <p>
                Internal Content, Inquiry, and Governance System for the official
                digital district office of Atty. Bel Zamora.
            </p>
        </div>

        <div class="district-login-trust-grid" aria-label="Administrative safeguards">
            <span class="district-trust-badge">
                <x-filament::icon icon="heroicon-m-shield-check" />
                Verified access
            </span>
            <span class="district-trust-badge">
                <x-filament::icon icon="heroicon-m-clock" />
                Staff activity tracked
            </span>
            <span class="district-trust-badge">
                <x-filament::icon icon="heroicon-m-document-check" />
                Publishing governed
            </span>
        </div>

        <div class="district-governance-warning district-login-message">
            <x-filament::icon icon="heroicon-m-lock-closed" />
            <div>
                <strong>Authorized personnel only.</strong>
                <span>Use this system for official District Office content, inquiry handling, review, approval, and audit work.</span>
            </div>
        </div>
    </section>

    <section class="district-login-panel" aria-label="Secure sign in form">
        <div class="district-login-brand">
            <img
                src="{{ asset('assets/images/brand/CongBelLogo.png') }}"
                alt="Cong. Bel Zamora Dunong at Dangal logo"
            >
            <div>
                <p>Atty. Bel Zamora</p>
                <span>Lone District of San Juan City</span>
            </div>
        </div>

        <div class="district-login-card-heading">
            <p>Secure staff sign in</p>
            <h2>Enter the admin console</h2>
            <span>Protected access for assigned office personnel.</span>
        </div>

        {{ FilamentView::renderHook(PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE, scopes: $this->getRenderHookScopes()) }}

        <x-filament-panels::form id="form" wire:submit="authenticate">
            {{ $this->form }}

            <x-filament-panels::form.actions
                :actions="$this->getCachedFormActions()"
                :full-width="$this->hasFullWidthFormActions()"
            />
        </x-filament-panels::form>

        {{ FilamentView::renderHook(PanelsRenderHook::AUTH_LOGIN_FORM_AFTER, scopes: $this->getRenderHookScopes()) }}

        <p class="district-login-footnote">
            Sign-in attempts are protected by rate limiting and session safeguards.
        </p>
    </section>
</div>
