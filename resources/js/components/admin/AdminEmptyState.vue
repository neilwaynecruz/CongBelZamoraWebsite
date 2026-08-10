<script setup lang="ts">
import { CheckCircle2 } from '@lucide/vue';

defineProps<{
    title: string;
    description: string;
}>();
</script>

<template>
    <div class="admin-empty-state">
        <span class="admin-empty-state__icon"><CheckCircle2 aria-hidden="true" /></span>
        <div>
            <h3>{{ title }}</h3>
            <p>{{ description }}</p>
        </div>
    </div>
</template>
