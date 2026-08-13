<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowLeft,
    ExternalLink,
    FileWarning,
    Fingerprint,
    ShieldCheck,
    UserRound,
} from '@lucide/vue';
import AdminStatusBadge from '@/components/admin/AdminStatusBadge.vue';
import type { AdminConsoleContext } from '@/types/admin-console';

type AuditValue = string | number | boolean | null | Record<string, unknown>;

type AuditLogDetail = {
    id: number;
    event: string;
    eventLabel: string;
    actor: {
        id: number | null;
        name: string;
        email: string | null;
    };
    auditable: {
        type: string | null;
        label: string;
        id: number | null;
        title: string;
    };
    network: {
        ipAddressHash: string | null;
        userAgentHash: string | null;
    };
    occurredAt: string | null;
    oldValues: Record<string, unknown>;
    newValues: Record<string, unknown>;
    metadata: Record<string, unknown>;
    diffRows: {
        key: string;
        old: AuditValue;
        new: AuditValue;
        changed: boolean;
    }[];
    filamentFallbackUrl: string | null;
};

defineProps<{
    adminConsole: AdminConsoleContext;
    auditLog: AuditLogDetail;
}>();

const displayValue = (value: AuditValue) => {
    if (value === null || value === undefined) {
        return 'null';
    }

    if (typeof value === 'object') {
        return JSON.stringify(value, null, 2);
    }

    return String(value);
};

const formatJson = (value: Record<string, unknown>) =>
    JSON.stringify(value, null, 2);
</script>

<template>
    <Head :title="`${auditLog.eventLabel} · Activity Event`" />

    <div class="admin-page admin-dossier-page">
        <Link href="/admin-console/audit" class="admin-back-link">
            <ArrowLeft aria-hidden="true" />Back to activity log
        </Link>

        <header class="admin-dossier-header admin-dossier-header--program">
            <div>
                <p class="admin-kicker">Activity event #{{ auditLog.id }}</p>
                <h1>{{ auditLog.eventLabel }}</h1>
                <p>
                    {{ auditLog.event }} recorded for
                    {{ auditLog.auditable.label }}.
                </p>
            </div>
            <div class="admin-dossier-header__status">
                <AdminStatusBadge value="info" :label="auditLog.event" />
                <AdminStatusBadge
                    value="healthy"
                    label="Sensitive values hidden"
                />
            </div>
        </header>

        <div class="admin-dossier-grid admin-program-dossier-grid">
            <div class="admin-dossier-grid__main">
                <section class="admin-panel admin-record-section">
                    <header>
                        <div>
                            <ShieldCheck aria-hidden="true" />
                            <h2>Event details</h2>
                        </div>
                    </header>
                    <dl class="admin-record-grid">
                        <div>
                            <dt>Event</dt>
                            <dd>{{ auditLog.event }}</dd>
                        </div>
                        <div>
                            <dt>Occurred</dt>
                            <dd>{{ auditLog.occurredAt ?? 'Not recorded' }}</dd>
                        </div>
                        <div>
                            <dt>Record type</dt>
                            <dd>{{ auditLog.auditable.label }}</dd>
                        </div>
                        <div>
                            <dt>Record</dt>
                            <dd>
                                {{ auditLog.auditable.title }}
                                <template v-if="auditLog.auditable.id">
                                    #{{ auditLog.auditable.id }}
                                </template>
                            </dd>
                        </div>
                    </dl>
                </section>

                <section class="admin-panel admin-record-section">
                    <header>
                        <div>
                            <FileWarning aria-hidden="true" />
                            <h2>What changed</h2>
                        </div>
                        <span class="admin-trust-marker">
                            <ShieldCheck aria-hidden="true" />Sensitive values
                            hidden
                        </span>
                    </header>
                    <div
                        v-if="auditLog.diffRows.length"
                        class="admin-responsive-table"
                    >
                        <table>
                            <thead>
                                <tr>
                                    <th>Field</th>
                                    <th>Old value</th>
                                    <th>New value</th>
                                    <th>State</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="row in auditLog.diffRows"
                                    :key="row.key"
                                >
                                    <td data-label="Key">
                                        <strong>{{ row.key }}</strong>
                                    </td>
                                    <td data-label="Old value">
                                        <code>{{ displayValue(row.old) }}</code>
                                    </td>
                                    <td data-label="New value">
                                        <code>{{ displayValue(row.new) }}</code>
                                    </td>
                                    <td data-label="State">
                                        <AdminStatusBadge
                                            :value="
                                                row.changed
                                                    ? 'warning'
                                                    : 'healthy'
                                            "
                                            :label="
                                                row.changed ? 'Changed' : 'Same'
                                            "
                                        />
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <p v-else class="admin-program-fallback-note">
                        This event did not record old and new values.
                    </p>
                </section>
            </div>

            <aside class="admin-dossier-grid__rail">
                <section class="admin-panel admin-program-source-panel">
                    <header>
                        <p class="admin-kicker">Actor</p>
                        <h2>Staff or system source</h2>
                    </header>
                    <dl class="admin-fact-list">
                        <div>
                            <dt>Name</dt>
                            <dd>{{ auditLog.actor.name }}</dd>
                        </div>
                        <div>
                            <dt>Email</dt>
                            <dd>
                                {{ auditLog.actor.email ?? 'System event' }}
                            </dd>
                        </div>
                    </dl>
                    <UserRound
                        aria-hidden="true"
                        class="admin-report-rail-icon"
                    />
                </section>

                <section class="admin-panel admin-program-source-panel">
                    <header>
                        <p class="admin-kicker">Network fingerprints</p>
                        <h2>Privacy-safe trace</h2>
                    </header>
                    <dl class="admin-fact-list">
                        <div>
                            <dt>IP fingerprint</dt>
                            <dd>
                                {{
                                    auditLog.network.ipAddressHash ??
                                    'Not recorded'
                                }}
                            </dd>
                        </div>
                        <div>
                            <dt>Browser fingerprint</dt>
                            <dd>
                                {{
                                    auditLog.network.userAgentHash ??
                                    'Not recorded'
                                }}
                            </dd>
                        </div>
                    </dl>
                    <Fingerprint
                        aria-hidden="true"
                        class="admin-report-rail-icon"
                    />
                </section>

                <section class="admin-panel admin-program-source-panel">
                    <header>
                        <p class="admin-kicker">Extra event details</p>
                        <h2>Safe event data</h2>
                    </header>
                    <pre class="admin-audit-json">{{
                        formatJson(auditLog.metadata)
                    }}</pre>
                </section>

                <section class="admin-panel admin-program-source-panel">
                    <header>
                        <p class="admin-kicker">Emergency admin</p>
                        <h2>Older admin status</h2>
                    </header>
                    <p class="admin-program-fallback-note">
                        Custom activity viewing is available. The older admin
                        area remains restricted until final staff sign-off is
                        complete.
                    </p>
                    <a
                        v-if="auditLog.filamentFallbackUrl"
                        :href="auditLog.filamentFallbackUrl"
                        class="admin-button admin-button--quiet admin-program-workflow-link"
                    >
                        Open emergency audit view<ExternalLink
                            aria-hidden="true"
                        />
                    </a>
                </section>
            </aside>
        </div>
    </div>
</template>
