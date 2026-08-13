<script setup lang="ts">
import { CheckCircle2, FileClock, ShieldAlert } from '@lucide/vue';
import AdminEmptyState from '@/components/admin/AdminEmptyState.vue';

defineProps<{
    snapshots: {
        id: number;
        versionNumber: number;
        type: { value: string; label: string };
        capturedBy: string;
        capturedAt: string | null;
        changeSummary: string | null;
        isPublishable: boolean;
        governanceIssues: string[];
        diffCount: number;
        canRollback: boolean;
    }[];
}>();
</script>

<template>
    <section
        class="admin-panel admin-version-ledger"
        aria-labelledby="version-ledger-title"
    >
        <header>
            <div>
                <p class="admin-kicker">Saved content history</p>
                <h2 id="version-ledger-title">Saved versions</h2>
            </div>
            <FileClock aria-hidden="true" />
        </header>

        <ol v-if="snapshots.length" class="admin-version-ledger__list">
            <li v-for="snapshot in snapshots" :key="snapshot.id">
                <div class="admin-version-ledger__rail">
                    <span>v{{ snapshot.versionNumber }}</span>
                </div>
                <div class="admin-version-ledger__entry">
                    <div class="admin-version-ledger__meta">
                        <strong>{{ snapshot.type.label }}</strong>
                        <span
                            :class="
                                snapshot.isPublishable
                                    ? 'is-ready'
                                    : 'is-blocked'
                            "
                        >
                            <CheckCircle2
                                v-if="snapshot.isPublishable"
                                aria-hidden="true"
                            />
                            <ShieldAlert v-else aria-hidden="true" />
                            {{
                                snapshot.isPublishable
                                    ? 'Passed at capture'
                                    : `${snapshot.governanceIssues.length} issue${snapshot.governanceIssues.length === 1 ? '' : 's'} to fix`
                            }}
                        </span>
                    </div>
                    <p>
                        {{
                            snapshot.changeSummary ??
                            'No change summary was recorded for this saved version.'
                        }}
                    </p>
                    <small>
                        {{ snapshot.capturedAt ?? 'Capture time unavailable' }}
                        · {{ snapshot.capturedBy }} ·
                        {{ snapshot.diffCount }} field change{{
                            snapshot.diffCount === 1 ? '' : 's'
                        }}
                    </small>
                    <span
                        v-if="snapshot.canRollback"
                        class="admin-version-ledger__eligible"
                        >Can be restored after review</span
                    >
                </div>
            </li>
        </ol>

        <AdminEmptyState
            v-else
            title="No saved versions yet"
            description="A saved version will be created when this record is submitted, approved, scheduled, published, archived, or safely restored."
        />
    </section>
</template>
