<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import AdminCommandPalette from '@/components/admin/AdminCommandPalette.vue';
import AdminMobileNav from '@/components/admin/AdminMobileNav.vue';
import AdminSidebar from '@/components/admin/AdminSidebar.vue';
import AdminTopbar from '@/components/admin/AdminTopbar.vue';
import { Toaster } from '@/components/ui/sonner';
import type { AdminConsoleContext } from '@/types/admin-console';

type PageProps = {
    adminConsole: AdminConsoleContext;
    attentionItems?: unknown[];
};

const page = usePage<PageProps>();
const mobileNavigationOpen = ref(false);
const commandPaletteOpen = ref(false);
const sidebarCollapsed = ref(false);

const context = computed(() => page.props.adminConsole);
const taskCount = computed(() => page.props.attentionItems?.length ?? 0);

const onGlobalKeydown = (event: KeyboardEvent) => {
    if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {
        event.preventDefault();
        mobileNavigationOpen.value = false;
        commandPaletteOpen.value = true;
    }
};

onMounted(() => document.addEventListener('keydown', onGlobalKeydown));
onBeforeUnmount(() => document.removeEventListener('keydown', onGlobalKeydown));
</script>

<template>
    <div
        data-admin-theme="command"
        class="admin-console-root"
        :class="{ 'admin-console-root--collapsed': sidebarCollapsed }"
    >
        <a href="#admin-main" class="admin-skip-link">Skip to admin content</a>

        <AdminSidebar
            class="admin-console-root__desktop-sidebar"
            :context="context"
            :collapsed="sidebarCollapsed"
        />

        <div class="admin-console-workspace">
            <AdminTopbar
                :context="context"
                :sidebar-collapsed="sidebarCollapsed"
                :task-count="taskCount"
                @open-navigation="mobileNavigationOpen = true"
                @toggle-sidebar="sidebarCollapsed = !sidebarCollapsed"
                @open-palette="commandPaletteOpen = true"
            />

            <main id="admin-main" class="admin-console-main" tabindex="-1">
                <slot />
            </main>
        </div>

        <AdminMobileNav
            :open="mobileNavigationOpen"
            :context="context"
            @close="mobileNavigationOpen = false"
        />
        <AdminCommandPalette
            :open="commandPaletteOpen"
            :navigation="context.navigation"
            :search-url="context.searchUrl"
            @close="commandPaletteOpen = false"
        />
        <Toaster
            class="admin-toaster"
            position="top-right"
            theme="light"
            rich-colors
            close-button
            :duration="6000"
        />
    </div>
</template>
