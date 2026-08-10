<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { AlertTriangle, CheckCircle2, Info, ShieldCheck } from '@lucide/vue';
import AdminEmptyState from '@/components/admin/AdminEmptyState.vue';
import AdminMetricCard from '@/components/admin/AdminMetricCard.vue';
import AdminPageHeader from '@/components/admin/AdminPageHeader.vue';
import AdminStatusBadge from '@/components/admin/AdminStatusBadge.vue';
import type { AdminConsoleContext } from '@/types/admin-console';

defineProps<{ adminConsole: AdminConsoleContext }>();
</script>

<template>
    <Head title="Admin Design System" />
    <div class="admin-page">
        <AdminPageHeader
            eyebrow="Internal product standard"
            title="Civic Ledger Command system"
            description="The approved components, semantic colors, typography, focus behavior, and state patterns for the custom District Office administration experience."
        />

        <section class="admin-design-section">
            <header>
                <p class="admin-kicker">Foundations</p>
                <h2>Semantic palette</h2>
            </header>
            <div class="admin-token-grid">
                <div data-token="navy">
                    <span></span><strong>Shell navy</strong
                    ><small>Structure and secure context</small>
                </div>
                <div data-token="blue">
                    <span></span><strong>Royal blue</strong
                    ><small>Primary system actions</small>
                </div>
                <div data-token="red">
                    <span></span><strong>Civic red</strong
                    ><small>Urgent and destructive states</small>
                </div>
                <div data-token="gold">
                    <span></span><strong>Governance gold</strong
                    ><small>Review, trust, and focus</small>
                </div>
                <div data-token="green">
                    <span></span><strong>Verified green</strong
                    ><small>Healthy and resolved states</small>
                </div>
                <div data-token="ivory">
                    <span></span><strong>Ledger ivory</strong
                    ><small>Readable work surfaces</small>
                </div>
            </div>
        </section>

        <section class="admin-design-section">
            <header>
                <p class="admin-kicker">Components</p>
                <h2>Status and action language</h2>
            </header>
            <div class="admin-design-row">
                <AdminStatusBadge value="pending" label="Pending" />
                <AdminStatusBadge value="for_review" label="For review" />
                <AdminStatusBadge value="overdue" label="Overdue" />
                <AdminStatusBadge value="resolved" label="Resolved" />
                <AdminStatusBadge value="archived" label="Archived" />
            </div>
            <div class="admin-design-row">
                <button
                    type="button"
                    class="admin-button admin-button--primary"
                >
                    <ShieldCheck aria-hidden="true" />Primary action
                </button>
                <button type="button" class="admin-button admin-button--quiet">
                    <Info aria-hidden="true" />Secondary action
                </button>
                <button type="button" class="admin-button admin-button--danger">
                    <AlertTriangle aria-hidden="true" />Destructive action
                </button>
            </div>
        </section>

        <section class="admin-design-section">
            <header>
                <p class="admin-kicker">Operational cards</p>
                <h2>Priority and metric states</h2>
            </header>
            <div class="admin-metric-grid">
                <AdminMetricCard
                    label="Standard metric"
                    :value="18"
                    description="Neutral operational information"
                    tone="info"
                />
                <AdminMetricCard
                    label="Review due"
                    :value="4"
                    description="Governance attention required"
                    tone="warning"
                />
                <AdminMetricCard
                    label="Overdue"
                    :value="2"
                    description="Immediate staff action required"
                    tone="danger"
                />
                <AdminMetricCard
                    label="Healthy"
                    :value="27"
                    description="Within policy and response windows"
                    tone="success"
                />
            </div>
            <AdminEmptyState
                title="A useful empty state"
                description="Every empty surface explains what the state means and what staff should expect next."
            />
        </section>

        <section class="admin-design-section">
            <header>
                <p class="admin-kicker">Form controls</p>
                <h2>Readable in every state</h2>
            </header>
            <div class="admin-design-form">
                <label class="admin-field"
                    ><span>Text field</span
                    ><input type="text" value="Official record title"
                /></label>
                <label class="admin-field"
                    ><span>Select field</span
                    ><select>
                        <option>For review</option>
                    </select></label
                >
                <label class="admin-field"
                    ><span>Disabled field</span
                    ><input type="text" value="System controlled" disabled
                /></label>
                <label class="admin-field"
                    ><span>Error example</span
                    ><input
                        type="text"
                        aria-invalid="true"
                        value="Missing source"
                    /><small class="admin-field__error"
                        >Add an official source before publishing.</small
                    ></label
                >
            </div>
            <p class="admin-design-note">
                <CheckCircle2 aria-hidden="true" />This page is restricted to
                super administrators and exists to prevent future design drift.
            </p>
        </section>
    </div>
</template>
