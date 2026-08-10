<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
    value: string;
    label?: string;
}>();

const tone = computed(() => {
    if (['resolved', 'approved', 'published', 'open', 'healthy'].includes(props.value)) {
        return 'success';
    }

    if (['urgent', 'overdue', 'rejected', 'danger', 'blocked'].includes(props.value)) {
        return 'danger';
    }

    if (
        ['for_review', 'needs_revision', 'high', 'warning', 'stale', 'scheduled'].includes(
            props.value,
        )
    ) {
        return 'warning';
    }

    if (['closed', 'archived', 'anonymized'].includes(props.value)) {
return 'neutral';
}

    return 'info';
});
</script>

<template>
    <span class="admin-status-badge" :data-tone="tone">
        <span aria-hidden="true"></span>
        {{ label ?? value }}
    </span>
</template>
