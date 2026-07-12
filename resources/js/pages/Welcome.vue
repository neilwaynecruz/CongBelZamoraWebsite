<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Development Baseline" />

    <div class="min-h-screen bg-surface-soft text-text-primary">
        <header class="border-b border-border-default bg-brand-white">
            <div
                class="mx-auto flex max-w-5xl flex-col gap-2 px-6 py-8 sm:flex-row sm:items-end sm:justify-between"
            >
                <div>
                    <p
                        class="text-sm font-medium tracking-wide text-brand-royal uppercase"
                    >
                        Phase 0 Baseline
                    </p>
                    <h1
                        class="font-serif text-3xl font-semibold text-brand-navy sm:text-4xl"
                    >
                        Bel Zamora District Office
                    </h1>
                </div>
                <p class="text-sm text-text-muted">
                    Development environment — not public content
                </p>
            </div>
        </header>

        <main class="mx-auto max-w-5xl px-6 py-10">
            <section
                class="rounded-xl border border-border-default bg-brand-white p-8 shadow-sm"
            >
                <h2 class="font-serif text-2xl font-semibold text-brand-navy">
                    Laravel · Vue · Inertia · SSR
                </h2>
                <p
                    class="mt-4 max-w-3xl text-base leading-relaxed text-text-primary"
                >
                    This repository contains the executable project baseline for
                    the Atty. Congresswoman Bel Zamora public-service website.
                    Feature modules, verified content, and staff workflows will
                    be implemented in later phases.
                </p>

                <dl class="mt-8 grid gap-4 sm:grid-cols-2">
                    <div class="rounded-lg bg-surface-soft p-4">
                        <dt class="text-sm font-medium text-brand-royal">
                            Public interface
                        </dt>
                        <dd class="mt-1 text-sm text-text-muted">
                            Vue 3, TypeScript, Inertia.js, and server-side
                            rendering
                        </dd>
                    </div>
                    <div class="rounded-lg bg-surface-soft p-4">
                        <dt class="text-sm font-medium text-brand-royal">
                            Staff interface
                        </dt>
                        <dd class="mt-1 text-sm text-text-muted">
                            Filament administration panel at
                            <code class="text-text-primary">/admin</code>
                        </dd>
                    </div>
                </dl>

                <p class="mt-8 text-sm text-text-muted">
                    Canonical project plan:
                    <code class="text-text-primary"
                        >bel_zamora_website_plan.md</code
                    >
                </p>
            </section>
        </main>
    </div>
</template>
