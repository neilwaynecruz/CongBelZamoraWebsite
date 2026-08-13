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

        <section class="admin-queue-strip" aria-label="Settings summary">
            <div>
                <ShieldCheck aria-hidden="true" />
                <span
                    ><small>Staff 2FA</small
                    ><strong>{{
                        settings.staffSecurity.twoFactorRequired
                            ? 'Required'
                            : 'Optional'
                    }}</strong></span
                >
            </div>
            <div>
                <ServerCog aria-hidden="true" />
                <span
                    ><small>Admin console</small
                    ><strong>{{
                        settings.adminConsole.enabled ? 'Enabled' : 'Disabled'
                    }}</strong></span
                >
            </div>
            <div>
                <Database aria-hidden="true" />
                <span
                    ><small>Retention</small
                    ><strong
                        >{{
                            settings.privacy.inquiryRetentionDays
                        }}
                        days</strong
                    ></span
                >
            </div>
            <div>
                <LockKeyhole aria-hidden="true" />
                <span
                    ><small>Writes</small
                    ><strong>{{
                        writeAccess.enabled ? 'Enabled' : 'Locked'
                    }}</strong></span
                >
            </div>
        </section>

        <div class="admin-dossier-grid admin-program-dossier-grid">
            <div class="admin-dossier-grid__main">
                <section class="admin-panel admin-record-section">
                    <header>
                        <div>
                            <ShieldCheck aria-hidden="true" />
                            <h2>Staff security</h2>
                        </div>
                    </header>
                    <dl class="admin-record-grid">
                        <div>
                            <dt>2FA required</dt>
                            <dd>
                                {{
                                    settings.staffSecurity.twoFactorRequired
                                        ? 'Yes'
                                        : 'No'
                                }}
                            </dd>
                        </div>
                        <div>
                            <dt>Database notifications</dt>
                            <dd>
                                {{
                                    settings.staffSecurity.databaseNotifications
                                        ? 'Enabled'
                                        : 'Disabled'
                                }}
                            </dd>
                        </div>
                        <div>
                            <dt>Emergency admin access</dt>
                            <dd>
                                {{
                                    settings.adminConsole
                                        .filamentFallbackEnabled
                                        ? 'Enabled'
                                        : 'Disabled'
                                }}
                            </dd>
                        </div>
                        <div>
                            <dt>Write access</dt>
                            <dd>{{ writeAccess.reason }}</dd>
                        </div>
                    </dl>
                </section>

                <section class="admin-panel admin-record-section">
                    <header>
                        <div>
                            <ServerCog aria-hidden="true" />
                            <h2>Environment</h2>
                        </div>
                    </header>
                    <dl class="admin-record-grid">
                        <div>
                            <dt>Environment</dt>
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
                            <dt>Background jobs</dt>
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
                            <dt>Mail</dt>
                            <dd>{{ settings.environment.mailMailer }}</dd>
                        </div>
                    </dl>
                </section>
            </div>

            <aside class="admin-dossier-grid__rail">
                <section class="admin-panel admin-program-source-panel">
                    <header>
                        <p class="admin-kicker">Public content</p>
                        <h2>Cache and media</h2>
                    </header>
                    <dl class="admin-fact-list">
                        <div>
                            <dt>Public page cache time</dt>
                            <dd>
                                {{ settings.cache.publicTtlSeconds }} seconds
                            </dd>
                        </div>
                        <div>
                            <dt>Max upload</dt>
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
                    <p class="admin-program-fallback-note">
                        Allowed media types:
                        {{ settings.media.allowedMimetypes.join(', ') }}
                    </p>
                </section>

                <section class="admin-panel admin-program-source-panel">
                    <header>
                        <p class="admin-kicker">Jobs</p>
                        <h2>Background job defaults</h2>
                    </header>
                    <dl class="admin-fact-list">
                        <div>
                            <dt>Attempts</dt>
                            <dd>{{ settings.jobs.tries }}</dd>
                        </div>
                        <div>
                            <dt>Wait before retry</dt>
                            <dd>{{ settings.jobs.backoffSeconds }} seconds</dd>
                        </div>
                        <div>
                            <dt>Timeout</dt>
                            <dd>{{ settings.jobs.timeoutSeconds }} seconds</dd>
                        </div>
                    </dl>
                </section>

                <section class="admin-panel admin-program-source-panel">
                    <header>
                        <p class="admin-kicker">Mail and emergency access</p>
                        <h2>Operational notes</h2>
                    </header>
                    <p class="admin-program-fallback-note">
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
                </section>
            </aside>
        </div>
    </div>
</template>
