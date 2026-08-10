<script setup lang="ts">
defineProps<{
    label: string;
    value: number;
    description: string;
    tone?: 'info' | 'warning' | 'danger' | 'success';
}>();
</script>

<template>
    <article class="admin-metric-card" :data-tone="tone ?? 'info'">
        <div class="admin-metric-card__rail" aria-hidden="true"></div>
        <p>{{ label }}</p>
        <strong>{{ value.toLocaleString() }}</strong>
        <span>{{ description }}</span>
    </article>
</template>
