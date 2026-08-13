<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    ArchiveRestore,
    ChartColumn,
    ClipboardCheck,
    ExternalLink,
    FileText,
    Gauge,
    Inbox,
    Landmark,
    Link2,
    MapPinned,
    Newspaper,
    Palette,
    Scale,
    Settings,
    ShieldCheck,
    UsersRound,
} from '@lucide/vue';
import { computed } from 'vue';
import type { AdminConsoleContext } from '@/types/admin-console';
import type { Component } from 'vue';

withDefaults(
    defineProps<{
        context: AdminConsoleContext;
        collapsed?: boolean;
        mobile?: boolean;
    }>(),
    {
        collapsed: false,
        mobile: false,
    },
);

defineEmits<{ navigate: [] }>();

const page = usePage();
const currentPath = computed(() => page.url.split('?')[0]);

const iconMap: Record<string, Component> = {
    gauge: Gauge,
    inbox: Inbox,
    approval: ClipboardCheck,
    programs: Landmark,
    projects: MapPinned,
    legislation: Scale,
    news: Newspaper,
    publicPages: FileText,
    source: Link2,
    reference: ArchiveRestore,
    reports: ChartColumn,
    audit: ShieldCheck,
    users: UsersRound,
    settings: Settings,
    palette: Palette,
    legacy: ArchiveRestore,
};

const isActive = (href: string) => {
    const path = new URL(href, window.location.origin).pathname;

    if (path === '/admin-console') {
        return currentPath.value === path;
    }

    return (
        currentPath.value === path || currentPath.value.startsWith(`${path}/`)
    );
};
</script>

<template>
    <aside
        class="admin-sidebar"
        :class="{
            'admin-sidebar--collapsed': collapsed && !mobile,
            'admin-sidebar--mobile': mobile,
        }"
        aria-label="District Office administration"
    >
        <div class="admin-sidebar__identity">
            <img
                src="/assets/images/brand/House_of_Representative_Logo_Seal.svg"
                alt="House of Representatives seal"
                width="48"
                height="48"
                class="admin-sidebar__seal"
            />
            <div v-if="!collapsed || mobile" class="min-w-0">
                <p class="admin-sidebar__eyebrow">District Office</p>
                <p class="admin-sidebar__brand">Command System</p>
            </div>
        </div>

        <nav class="admin-sidebar__nav" aria-label="Admin console navigation">
            <section
                v-for="group in context.navigation"
                :key="group.label"
                class="admin-nav-group"
            >
                <h2 v-if="!collapsed || mobile" class="admin-nav-group__label">
                    {{ group.label }}
                </h2>
                <div class="admin-nav-group__items">
                    <Link
                        v-for="item in group.items"
                        :key="item.href"
                        :href="item.href"
                        class="admin-nav-item"
                        :class="{
                            'admin-nav-item--active': isActive(item.href),
                        }"
                        :aria-current="isActive(item.href) ? 'page' : undefined"
                        :title="collapsed && !mobile ? item.label : undefined"
                        @click="$emit('navigate')"
                    >
                        <component
                            :is="iconMap[item.icon] ?? Gauge"
                            class="admin-nav-item__icon"
                            aria-hidden="true"
                        />
                        <span
                            v-if="!collapsed || mobile"
                            class="admin-nav-item__body"
                        >
                            <span class="admin-nav-item__line">
                                <span>{{ item.label }}</span>
                                <span
                                    v-if="item.badge"
                                    class="admin-nav-item__badge"
                                >
                                    {{ item.badge }}
                                </span>
                                <ExternalLink
                                    v-if="item.external"
                                    class="size-3.5"
                                    aria-hidden="true"
                                />
                            </span>
                            <span class="admin-nav-item__description">
                                {{ item.description }}
                            </span>
                        </span>
                    </Link>
                </div>
            </section>
        </nav>

        <div v-if="!collapsed || mobile" class="admin-sidebar__footer">
            <div class="admin-sidebar__security-dot" aria-hidden="true"></div>
            <div>
                <p>Protected staff workspace</p>
                <span>Role and activity controls are active.</span>
            </div>
        </div>
    </aside>
</template>
