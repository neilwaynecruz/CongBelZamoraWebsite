<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import {
    Database,
    LockKeyhole,
    Mail,
    ServerCog,
    Settings,
    ShieldCheck,
} from '@lucide/vue';
import AdminPageHeader from '@/components/admin/AdminPageHeader.vue';
import AdminStatusBadge from '@/components/admin/AdminStatusBadge.vue';
import type { AdminConsoleContext } from '@/types/admin-console';

defineProps<{
    adminConsole: AdminConsoleContext;
    settings: {
        staffSecurity: {
            twoFactorRequired: boolean;
            databaseNotifications: boolean;
        };
        adminConsole: {
            enabled: boolean;
            filamentFallbackEnabled: boolean;
            modules: Record<string, boolean>;
        };
        privacy: {
            inquiryRetentionDays: number;
        };
        media: {
            maxUploadKilobytes: number;
            allowedMimetypes: string[];
            newsDirectory: string;
            projectEvidenceDirectory: string;
        };
        cache: {
            publicTtlSeconds: number;
        };
        jobs: {
            tries: number;
            backoffSeconds: number;
            timeoutSeconds: number;
        };
        environment: {
            appEnv: string;
            debugEnabled: boolean;
            queueConnection: string;
            cacheStore: string;
            filesystemDisk: string;
            mailMailer: string;
        };
    };
    writeAccess: {
        enabled: boolean;
        reason: string;
    };
    filamentFallbackUrl: string | null;
}>();
</script>

<template>
    <Head title="Admin Console Settings" />

    <div class="admin-page">
        <AdminPageHeader
            eyebrow="Settings"
            title="Launch and security settings"
            description="Review current admin settings without showing secrets or allowing unapproved changes."
        >
            <template #actions>
                <span class="admin-trust-marker">
                    <Settings aria-hidden="true" />Read-only
                </span>
            </template>
        </AdminPageHeader>

        <section class="admin-settings-summary" aria-label="Settings summary">
            <article class="admin-settings-summary__item">
                <span class="admin-settings-summary__icon">
                    <ShieldCheck aria-hidden="true" />
                </span>
                <span class="admin-settings-summary__copy">
                    <small>Staff sign-in security</small>
                    <strong>
                        2FA
                        {{
                            settings.staffSecurity.twoFactorRequired
                                ? 'required'
                                : 'optional'
                        }}
                    </strong>
                </span>
            </article>
            <article class="admin-settings-summary__item">
                <span class="admin-settings-summary__icon">
                    <ServerCog aria-hidden="true" />
                </span>
                <span class="admin-settings-summary__copy">
                    <small>Staff workspace</small>
                    <strong>
                        {{
                            settings.adminConsole.enabled
                                ? 'Admin console enabled'
                                : 'Admin console disabled'
                        }}
                    </strong>
                </span>
            </article>
            <article class="admin-settings-summary__item">
                <span class="admin-settings-summary__icon">
                    <Database aria-hidden="true" />
                </span>
                <span class="admin-settings-summary__copy">
                    <small>Inquiry data retention</small>
                    <strong>
                        {{ settings.privacy.inquiryRetentionDays }} days
                    </strong>
                </span>
            </article>
            <article class="admin-settings-summary__item">
                <span class="admin-settings-summary__icon">
                    <LockKeyhole aria-hidden="true" />
                </span>
                <span class="admin-settings-summary__copy">
                    <small>Configuration changes</small>
                    <strong>
                        {{
                            writeAccess.enabled
                                ? 'Changes allowed'
                                : 'Changes locked'
                        }}
                    </strong>
                </span>
            </article>
        </section>

        <div class="admin-settings-grid">
            <section
                class="admin-panel admin-settings-panel admin-settings-panel--security"
            >
                <header class="admin-settings-panel__header">
                    <span class="admin-settings-panel__icon">
                        <ShieldCheck aria-hidden="true" />
                    </span>
                    <div>
                        <p class="admin-kicker">Access protection</p>
                        <h2>Staff security</h2>
                    </div>
                </header>
                <dl class="admin-record-grid admin-settings-record-grid">
                    <div>
                        <dt>Two-step sign-in</dt>
                        <dd>
                            {{
                                settings.staffSecurity.twoFactorRequired
                                    ? 'Required for staff'
                                    : 'Optional for staff'
                            }}
                        </dd>
                    </div>
                    <div>
                        <dt>Saved staff notifications</dt>
                        <dd>
                            {{
                                settings.staffSecurity.databaseNotifications
                                    ? 'Enabled'
                                    : 'Disabled'
                            }}
                        </dd>
                    </div>
                    <div>
                        <dt>Emergency admin fallback</dt>
                        <dd>
                            {{
                                settings.adminConsole.filamentFallbackEnabled
                                    ? 'Enabled'
                                    : 'Disabled'
                            }}
                        </dd>
                    </div>
                    <div>
                        <dt>Who can change settings</dt>
                        <dd>{{ writeAccess.reason }}</dd>
                    </div>
                </dl>
            </section>

            <section
                class="admin-panel admin-settings-panel admin-settings-panel--environment"
            >
                <header class="admin-settings-panel__header">
                    <span class="admin-settings-panel__icon">
                        <ServerCog aria-hidden="true" />
                    </span>
                    <div>
                        <p class="admin-kicker">System connections</p>
                        <h2>Environment</h2>
                    </div>
                </header>
                <dl class="admin-record-grid admin-settings-record-grid">
                    <div>
                        <dt>Current environment</dt>
                        <dd>{{ settings.environment.appEnv }}</dd>
                    </div>
                    <div>
                        <dt>Debug</dt>
                        <dd>
                            <AdminStatusBadge
                                :value="
                                    settings.environment.debugEnabled
                                        ? 'warning'
                                        : 'healthy'
                                "
                                :label="
                                    settings.environment.debugEnabled
                                        ? 'Enabled'
                                        : 'Disabled'
                                "
                            />
                        </dd>
                    </div>
                    <div>
                        <dt>Background work</dt>
                        <dd>{{ settings.environment.queueConnection }}</dd>
                    </div>
                    <div>
                        <dt>Saved page cache</dt>
                        <dd>{{ settings.environment.cacheStore }}</dd>
                    </div>
                    <div>
                        <dt>File storage</dt>
                        <dd>{{ settings.environment.filesystemDisk }}</dd>
                    </div>
                    <div>
                        <dt>Email delivery</dt>
                        <dd>{{ settings.environment.mailMailer }}</dd>
                    </div>
                </dl>
            </section>

            <section
                class="admin-panel admin-settings-panel admin-settings-panel--media"
            >
                <header class="admin-settings-panel__header">
                    <span class="admin-settings-panel__icon">
                        <Database aria-hidden="true" />
                    </span>
                    <div>
                        <p class="admin-kicker">Public content</p>
                        <h2>Page speed and uploads</h2>
                    </div>
                </header>
                <div class="admin-settings-panel__body">
                    <dl class="admin-settings-fact-list">
                        <div>
                            <dt>Saved public page time</dt>
                            <dd>
                                {{ settings.cache.publicTtlSeconds }} seconds
                            </dd>
                        </div>
                        <div>
                            <dt>Largest allowed upload</dt>
                            <dd>{{ settings.media.maxUploadKilobytes }} KB</dd>
                        </div>
                        <div>
                            <dt>News media directory</dt>
                            <dd>{{ settings.media.newsDirectory }}</dd>
                        </div>
                        <div>
                            <dt>Project evidence directory</dt>
                            <dd>
                                {{ settings.media.projectEvidenceDirectory }}
                            </dd>
                        </div>
                    </dl>
                    <p class="admin-settings-note">
                        <strong>Allowed image types:</strong>
                        {{ settings.media.allowedMimetypes.join(', ') }}
                    </p>
                </div>
            </section>

            <section
                class="admin-panel admin-settings-panel admin-settings-panel--jobs"
            >
                <header class="admin-settings-panel__header">
                    <span class="admin-settings-panel__icon">
                        <ServerCog aria-hidden="true" />
                    </span>
                    <div>
                        <p class="admin-kicker">Background work</p>
                        <h2>Retry limits</h2>
                    </div>
                </header>
                <div class="admin-settings-panel__body">
                    <dl class="admin-settings-fact-list">
                        <div>
                            <dt>Maximum attempts</dt>
                            <dd>{{ settings.jobs.tries }}</dd>
                        </div>
                        <div>
                            <dt>Wait between attempts</dt>
                            <dd>{{ settings.jobs.backoffSeconds }} seconds</dd>
                        </div>
                        <div>
                            <dt>Timeout</dt>
                            <dd>{{ settings.jobs.timeoutSeconds }} seconds</dd>
                        </div>
                    </dl>
                </div>
            </section>

            <section
                class="admin-panel admin-settings-panel admin-settings-panel--notes"
            >
                <header class="admin-settings-panel__header">
                    <span class="admin-settings-panel__icon">
                        <LockKeyhole aria-hidden="true" />
                    </span>
                    <div>
                        <p class="admin-kicker">Change control</p>
                        <h2>Why settings are read-only</h2>
                    </div>
                </header>
                <div class="admin-settings-panel__body">
                    <p class="admin-settings-note">
                        Settings are intentionally read-only here until the
                        office approves who can change them, how to undo them,
                        and how to log each change.
                    </p>
                    <a
                        v-if="filamentFallbackUrl"
                        :href="filamentFallbackUrl"
                        class="admin-button admin-button--quiet admin-program-workflow-link"
                    >
                        Open emergency admin<Mail aria-hidden="true" />
                    </a>
                </div>
            </section>
        </div>
    </div>
</template>
