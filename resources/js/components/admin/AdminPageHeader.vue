<script setup lang="ts">
defineProps<{
    eyebrow?: string;
    title: string;
    description: string;
}>();
</script>

<template>
    <header class="admin-page-header">
        <div>
            <p v-if="eyebrow" class="admin-kicker">{{ eyebrow }}</p>
            <h1>{{ title }}</h1>
            <p>{{ description }}</p>
        </div>
        <div v-if="$slots.actions" class="admin-page-header__actions">
            <slot name="actions" />
        </div>
    </header>
</template>
