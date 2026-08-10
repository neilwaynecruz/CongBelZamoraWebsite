<script setup lang="ts">
import { CheckCircle2, Columns3 } from '@lucide/vue';

type ComparisonRow = {
    field: string;
    label: string;
    published: unknown;
    current: unknown;
};

defineProps<{ comparison: ComparisonRow[] }>();

const formatValue = (value: unknown): string => {
    if (value === null || value === undefined || value === '') {
        return 'Not set';
    }

    if (typeof value === 'boolean') {
        return value ? 'Yes' : 'No';
    }

    if (typeof value === 'object') {
        return JSON.stringify(value, null, 2);
    }

    return String(value);
};
</script>

<template>
    <section
        class="admin-panel admin-comparison"
        aria-labelledby="content-comparison-title"
    >
        <header>
            <div>
                <p class="admin-kicker">Editorial comparison</p>
                <h2 id="content-comparison-title">
                    Published record vs current draft
                </h2>
            </div>
            <span class="admin-comparison__count"
                >{{ comparison.length }} changed field{{
                    comparison.length === 1 ? '' : 's'
                }}</span
            >
        </header>

        <div v-if="comparison.length" class="admin-comparison__table">
            <table>
                <thead>
                    <tr>
                        <th>Field</th>
                        <th>Published version</th>
                        <th>Current draft</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="row in comparison" :key="row.field">
                        <th scope="row">
                            <span>{{ row.label }}</span>
                            <small>{{ row.field }}</small>
                        </th>
                        <td data-label="Published version">
                            <pre>{{ formatValue(row.published) }}</pre>
                        </td>
                        <td data-label="Current draft">
                            <pre>{{ formatValue(row.current) }}</pre>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-else class="admin-comparison__empty">
            <CheckCircle2 aria-hidden="true" />
            <div>
                <strong>No changed fields detected</strong>
                <p>
                    The current record matches the latest published snapshot, or
                    this content has not yet produced a published comparison
                    baseline.
                </p>
            </div>
            <Columns3 aria-hidden="true" />
        </div>
    </section>
</template>
