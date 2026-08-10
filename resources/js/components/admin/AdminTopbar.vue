<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import {
    Bell,
    ChevronDown,
    Clock3,
    Command,
    LogOut,
    Menu,
    PanelLeftClose,
    PanelLeftOpen,
    Search,
} from '@lucide/vue';
import { onBeforeUnmount, onMounted, ref } from 'vue';
import type { AdminConsoleContext } from '@/types/admin-console';

const props = defineProps<{
    context: AdminConsoleContext;
    sidebarCollapsed: boolean;
    taskCount: number;
}>();

defineEmits<{
    openNavigation: [];
    toggleSidebar: [];
    openPalette: [trigger: HTMLElement | null];
}>();

const profileOpen = ref(false);
const profileButton = ref<HTMLButtonElement | null>(null);
const timeLabel = ref('');
let clock: number | undefined;

const updateTime = () => {
    timeLabel.value = new Intl.DateTimeFormat('en-PH', {
        timeZone: 'Asia/Manila',
        hour: 'numeric',
        minute: '2-digit',
        weekday: 'short',
    }).format(new Date());
};

const logout = () => router.post(props.context.logoutUrl);

const onDocumentClick = (event: MouseEvent) => {
    if (
        profileOpen.value &&
        profileButton.value &&
        !profileButton.value.parentElement?.contains(event.target as Node)
    ) {
        profileOpen.value = false;
    }
};

onMounted(() => {
    updateTime();
    clock = window.setInterval(updateTime, 30_000);
    document.addEventListener('click', onDocumentClick);
});

onBeforeUnmount(() => {
    if (clock) {
window.clearInterval(clock);
}

    document.removeEventListener('click', onDocumentClick);
});
</script>

<template>
    <header class="admin-topbar">
        <div class="admin-topbar__leading">
            <button
                type="button"
                class="admin-icon-button admin-topbar__mobile-menu"
                aria-label="Open navigation"
                @click="$emit('openNavigation')"
            >
                <Menu aria-hidden="true" />
            </button>
            <button
                type="button"
                class="admin-icon-button admin-topbar__collapse"
                :aria-label="sidebarCollapsed ? 'Expand sidebar' : 'Collapse sidebar'"
                @click="$emit('toggleSidebar')"
            >
                <PanelLeftOpen v-if="sidebarCollapsed" aria-hidden="true" />
                <PanelLeftClose v-else aria-hidden="true" />
            </button>

            <button
                type="button"
                class="admin-search-trigger"
                aria-label="Open command search"
                @click="$emit('openPalette', $event.currentTarget as HTMLElement)"
            >
                <Search aria-hidden="true" />
                <span>Search records or run a command</span>
                <span class="admin-search-trigger__shortcut">
                    <Command aria-hidden="true" />K
                </span>
            </button>
        </div>

        <div class="admin-topbar__actions">
            <div class="admin-topbar__time" aria-label="Current Manila time">
                <Clock3 aria-hidden="true" />
                <span>{{ timeLabel }}</span>
                <small>PHT</small>
            </div>

            <a
                href="#attention-queue"
                class="admin-icon-button admin-topbar__tasks"
                aria-label="View priority task queue"
            >
                <Bell aria-hidden="true" />
                <span v-if="taskCount > 0" class="admin-topbar__task-count">
                    {{ Math.min(taskCount, 99) }}
                </span>
            </a>

            <div class="admin-profile-menu">
                <button
                    ref="profileButton"
                    type="button"
                    class="admin-profile-menu__trigger"
                    :aria-expanded="profileOpen"
                    aria-haspopup="menu"
                    @click.stop="profileOpen = !profileOpen"
                >
                    <span class="admin-profile-menu__avatar">{{ context.user.initials }}</span>
                    <span class="admin-profile-menu__identity">
                        <strong>{{ context.user.name }}</strong>
                        <small>{{ context.user.roleLabel }}</small>
                    </span>
                    <ChevronDown aria-hidden="true" />
                </button>

                <div v-if="profileOpen" class="admin-profile-menu__popover" role="menu">
                    <div class="admin-profile-menu__summary">
                        <strong>{{ context.user.name }}</strong>
                        <span>{{ context.user.email }}</span>
                        <small>{{ context.user.roleLabel }}</small>
                    </div>
                    <button type="button" role="menuitem" @click="logout">
                        <LogOut aria-hidden="true" />
                        Sign out securely
                    </button>
                </div>
            </div>
        </div>
    </header>
</template>
