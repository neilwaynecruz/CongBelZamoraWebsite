<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { ArrowRight, Command, ExternalLink, Search, X } from '@lucide/vue';
import { computed, nextTick, onBeforeUnmount, ref, watch } from 'vue';
import type {
    AdminNavigationGroup,
    AdminNavigationItem,
} from '@/types/admin-console';

type SearchResult = {
    id: number | string;
    type: string;
    title: string;
    subtitle: string | null;
    href: string | null;
    usesLegacyFallback: boolean;
};

const props = defineProps<{
    open: boolean;
    navigation: AdminNavigationGroup[];
    searchUrl: string;
}>();

const emit = defineEmits<{ close: [] }>();
const dialog = ref<HTMLElement | null>(null);
const searchInput = ref<HTMLInputElement | null>(null);
const query = ref('');
const remoteResults = ref<SearchResult[]>([]);
const loading = ref(false);
const searchError = ref(false);
let searchTimer: number | undefined;
let abortController: AbortController | null = null;
let priorFocus: HTMLElement | null = null;

const commands = computed(() =>
    props.navigation.flatMap((group) =>
        group.items.map((item) => ({ ...item, group: group.label })),
    ),
);

const filteredCommands = computed(() => {
    const term = query.value.trim().toLowerCase();

    if (!term) {
        return commands.value;
    }

    return commands.value.filter((item) =>
        `${item.label} ${item.description} ${item.group}`
            .toLowerCase()
            .includes(term),
    );
});

const navigate = (item: AdminNavigationItem | SearchResult) => {
    if (!item.href) {
        return;
    }

    emit('close');

    if ('external' in item ? item.external : item.usesLegacyFallback) {
        window.location.assign(item.href);

        return;
    }

    router.visit(item.href);
};

const runSearch = async () => {
    const term = query.value.trim();
    remoteResults.value = [];
    searchError.value = false;

    if (term.length < 2) {
        return;
    }

    abortController?.abort();
    abortController = new AbortController();
    loading.value = true;

    try {
        const url = new URL(props.searchUrl, window.location.origin);
        url.searchParams.set('term', term);
        const response = await fetch(url, {
            signal: abortController.signal,
            headers: { Accept: 'application/json' },
            credentials: 'same-origin',
        });

        if (!response.ok) {
            throw new Error(`Search failed with ${response.status}`);
        }

        const payload = (await response.json()) as { data: SearchResult[] };
        remoteResults.value = payload.data;
    } catch (error) {
        if ((error as Error).name !== 'AbortError') {
            searchError.value = true;
        }
    } finally {
        loading.value = false;
    }
};

watch(query, () => {
    if (searchTimer) {
        window.clearTimeout(searchTimer);
    }

    searchTimer = window.setTimeout(runSearch, 280);
});

const focusableSelector =
    'a[href], button:not([disabled]), input:not([disabled]), [tabindex]:not([tabindex="-1"])';

const onKeydown = (event: KeyboardEvent) => {
    if (!props.open) {
        return;
    }

    if (event.key === 'Escape') {
        event.preventDefault();
        emit('close');

        return;
    }

    if (event.key !== 'Tab' || !dialog.value) {
        return;
    }

    const focusable = Array.from(
        dialog.value.querySelectorAll<HTMLElement>(focusableSelector),
    );
    const first = focusable[0];
    const last = focusable[focusable.length - 1];

    if (!first || !last) {
        return;
    }

    if (event.shiftKey && document.activeElement === first) {
        event.preventDefault();
        last.focus();
    } else if (!event.shiftKey && document.activeElement === last) {
        event.preventDefault();
        first.focus();
    }
};

watch(
    () => props.open,
    async (open) => {
        if (open) {
            priorFocus = document.activeElement as HTMLElement | null;
            document.body.style.overflow = 'hidden';
            document.addEventListener('keydown', onKeydown);
            await nextTick();
            searchInput.value?.focus();
        } else {
            query.value = '';
            remoteResults.value = [];
            document.body.style.overflow = '';
            document.removeEventListener('keydown', onKeydown);
            priorFocus?.focus();
        }
    },
);

onBeforeUnmount(() => {
    if (searchTimer) {
        window.clearTimeout(searchTimer);
    }

    abortController?.abort();
    document.body.style.overflow = '';
    document.removeEventListener('keydown', onKeydown);
});
</script>

<template>
    <Teleport to="body">
        <Transition name="admin-palette">
            <div v-if="open" class="admin-command-palette" role="presentation">
                <button
                    type="button"
                    class="admin-command-palette__backdrop"
                    aria-label="Close command search"
                    @click="$emit('close')"
                ></button>
                <section
                    ref="dialog"
                    class="admin-command-palette__dialog"
                    role="dialog"
                    aria-modal="true"
                    aria-labelledby="admin-command-title"
                >
                    <header class="admin-command-palette__header">
                        <div>
                            <p class="admin-kicker">District switchboard</p>
                            <h2 id="admin-command-title">
                                Find work and move quickly
                            </h2>
                        </div>
                        <button
                            type="button"
                            class="admin-icon-button"
                            aria-label="Close command search"
                            @click="$emit('close')"
                        >
                            <X aria-hidden="true" />
                        </button>
                    </header>

                    <label class="admin-command-palette__search">
                        <Search aria-hidden="true" />
                        <span class="sr-only">Search commands and records</span>
                        <input
                            ref="searchInput"
                            v-model="query"
                            type="search"
                            placeholder="Search commands, programs, projects, news..."
                            autocomplete="off"
                        />
                        <span><Command aria-hidden="true" />K</span>
                    </label>

                    <div class="admin-command-palette__results">
                        <section v-if="filteredCommands.length">
                            <h3>Commands</h3>
                            <button
                                v-for="item in filteredCommands"
                                :key="item.href"
                                type="button"
                                class="admin-command-result"
                                @click="navigate(item)"
                            >
                                <span>
                                    <strong>{{ item.label }}</strong>
                                    <small
                                        >{{ item.group }} ·
                                        {{ item.description }}</small
                                    >
                                </span>
                                <ExternalLink
                                    v-if="item.external"
                                    aria-hidden="true"
                                />
                                <ArrowRight v-else aria-hidden="true" />
                            </button>
                        </section>

                        <section v-if="query.trim().length >= 2">
                            <h3>Authorized records</h3>
                            <p
                                v-if="loading"
                                class="admin-command-palette__state"
                                role="status"
                            >
                                Searching authorized records...
                            </p>
                            <p
                                v-else-if="searchError"
                                class="admin-command-palette__state admin-command-palette__state--error"
                            >
                                Search is temporarily unavailable. Commands
                                remain available.
                            </p>
                            <button
                                v-for="result in remoteResults"
                                :key="`${result.type}-${result.id}`"
                                type="button"
                                class="admin-command-result"
                                :disabled="!result.href"
                                @click="navigate(result)"
                            >
                                <span>
                                    <strong>{{ result.title }}</strong>
                                    <small>
                                        {{ result.type }}
                                        <template v-if="result.subtitle">
                                            · {{ result.subtitle }}</template
                                        >
                                        <template
                                            v-if="result.usesLegacyFallback"
                                        >
                                            · Opens fallback</template
                                        >
                                    </small>
                                </span>
                                <ExternalLink
                                    v-if="result.href"
                                    aria-hidden="true"
                                />
                            </button>
                            <p
                                v-if="
                                    !loading &&
                                    !searchError &&
                                    remoteResults.length === 0
                                "
                                class="admin-command-palette__state"
                            >
                                No authorized records match this search.
                            </p>
                        </section>
                    </div>
                </section>
            </div>
        </Transition>
    </Teleport>
</template>
