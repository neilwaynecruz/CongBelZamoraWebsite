<script setup lang="ts">
import {
    AlertTriangle,
    CheckCircle2,
    FileCheck2,
    ShieldCheck,
} from '@lucide/vue';

defineProps<{
    readiness: {
        passed: boolean;
        issueCount: number;
        issues: string[];
    };
    policy: {
        label: string;
        reviewCadenceDays: number;
        sourcePolicy: string;
        requiredFields: string[];
    } | null;
}>();
</script>

<template>
    <section
        class="admin-panel admin-readiness"
        :class="
            readiness.passed
                ? 'admin-readiness--passed'
                : 'admin-readiness--blocked'
        "
        aria-labelledby="publish-readiness-title"
    >
        <header>
            <div>
                <p class="admin-kicker">Automatic publish check</p>
                <h2 id="publish-readiness-title">Ready to publish?</h2>
            </div>
            <span class="admin-readiness__state">
                <CheckCircle2 v-if="readiness.passed" aria-hidden="true" />
                <AlertTriangle v-else aria-hidden="true" />
                {{
                    readiness.passed
                        ? 'Ready for release'
                        : `${readiness.issueCount} blocker${readiness.issueCount === 1 ? '' : 's'}`
                }}
            </span>
        </header>

        <div v-if="readiness.passed" class="admin-readiness__clear">
            <ShieldCheck aria-hidden="true" />
            <div>
                <strong>Automated checks passed</strong>
                <p>
                    Owner, source, review date, active dates, and change
                    details are present. A person still needs to review before
                    publishing.
                </p>
            </div>
        </div>

        <ol v-else class="admin-readiness__issues">
            <li v-for="(issue, index) in readiness.issues" :key="issue">
                <span>{{ String(index + 1).padStart(2, '0') }}</span>
                <p>{{ issue }}</p>
            </li>
        </ol>

        <div v-if="policy" class="admin-readiness__policy">
            <FileCheck2 aria-hidden="true" />
            <div>
                <strong>{{ policy.label }} policy</strong>
                <p>{{ policy.sourcePolicy }}</p>
                <small
                    >Review again every {{ policy.reviewCadenceDays }} days ·
                    {{ policy.requiredFields.length }} required publishing
                    fields</small
                >
            </div>
        </div>
        <div v-else class="admin-readiness__policy">
            <FileCheck2 aria-hidden="true" />
            <div>
                <strong>Manual review rule</strong>
                <p>
                    No automatic rule is set for this module. Review the source
                    list and approval checklist before release.
                </p>
            </div>
        </div>
    </section>
</template>
