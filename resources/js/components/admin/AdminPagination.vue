<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight } from '@lucide/vue';

defineProps<{
    links: {
        prev?: string | null;
        next?: string | null;
    };
    meta: {
        current_page: number;
        last_page: number;
        from: number | null;
        to: number | null;
        total: number;
    };
}>();
</script>

<template>
    <nav class="admin-pagination" aria-label="Pagination">
        <p>
            <strong>{{ meta.from ?? 0 }}–{{ meta.to ?? 0 }}</strong>
            of {{ meta.total }} records
        </p>
        <div>
            <Link
                v-if="links.prev"
                :href="links.prev"
                preserve-scroll
                class="admin-button admin-button--quiet"
                aria-label="Previous page"
            >
                <ChevronLeft aria-hidden="true" />Previous
            </Link>
            <span
                v-else
                class="admin-button admin-button--quiet"
                aria-disabled="true"
            >
                <ChevronLeft aria-hidden="true" />Previous
            </span>
            <span>Page {{ meta.current_page }} of {{ meta.last_page }}</span>
            <Link
                v-if="links.next"
                :href="links.next"
                preserve-scroll
                class="admin-button admin-button--quiet"
                aria-label="Next page"
            >
                Next<ChevronRight aria-hidden="true" />
            </Link>
            <span
                v-else
                class="admin-button admin-button--quiet"
                aria-disabled="true"
            >
                Next<ChevronRight aria-hidden="true" />
            </span>
        </div>
    </nav>
</template>
