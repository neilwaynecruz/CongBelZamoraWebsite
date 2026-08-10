<script setup lang="ts">
import { LoaderCircle, X } from '@lucide/vue';
import { nextTick, onBeforeUnmount, ref, watch } from 'vue';

const props = withDefaults(
    defineProps<{
        open: boolean;
        kicker: string;
        title: string;
        description: string;
        confirmLabel: string;
        busy?: boolean;
        tone?: 'primary' | 'danger' | 'success' | 'warning';
        closeOnBackdrop?: boolean;
    }>(),
    {
        busy: false,
        tone: 'primary',
        closeOnBackdrop: true,
    },
);

const emit = defineEmits<{
    close: [];
    confirm: [];
}>();

const dialog = ref<HTMLElement | null>(null);
let priorFocus: HTMLElement | null = null;
const focusableSelector =
    'a[href], button:not([disabled]), textarea:not([disabled]), input:not([disabled]), select:not([disabled]), [tabindex]:not([tabindex="-1"])';

const close = () => {
    if (!props.busy) {
        emit('close');
    }
};

const onBackdrop = () => {
    if (props.closeOnBackdrop) {
        close();
    }
};

const onKeydown = (event: KeyboardEvent) => {
    if (!props.open) {
        return;
    }

    if (event.key === 'Escape') {
        event.preventDefault();
        close();

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
            const firstField = dialog.value?.querySelector<HTMLElement>(
                '[data-autofocus], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), button:not([disabled])',
            );
            firstField?.focus();
        } else {
            document.body.style.overflow = '';
            document.removeEventListener('keydown', onKeydown);
            priorFocus?.focus();
        }
    },
    { immediate: true },
);

onBeforeUnmount(() => {
    document.body.style.overflow = '';
    document.removeEventListener('keydown', onKeydown);
    priorFocus?.focus();
});
</script>

<template>
    <Teleport to="[data-admin-theme='command']">
        <Transition name="admin-dialog">
            <div v-if="open" class="admin-action-dialog" role="presentation">
                <button
                    type="button"
                    class="admin-action-dialog__backdrop"
                    :aria-label="closeOnBackdrop ? `Cancel ${title}` : undefined"
                    :tabindex="closeOnBackdrop ? 0 : -1"
                    @click="onBackdrop"
                ></button>

                <section
                    ref="dialog"
                    class="admin-action-dialog__panel"
                    :class="`admin-action-dialog__panel--${tone}`"
                    role="dialog"
                    aria-modal="true"
                    aria-labelledby="admin-action-dialog-title"
                    aria-describedby="admin-action-dialog-description"
                >
                    <header class="admin-action-dialog__header">
                        <div>
                            <p class="admin-kicker">{{ kicker }}</p>
                            <h2 id="admin-action-dialog-title">{{ title }}</h2>
                            <p id="admin-action-dialog-description">{{ description }}</p>
                        </div>
                        <button
                            type="button"
                            class="admin-icon-button"
                            :aria-label="`Cancel ${title}`"
                            :disabled="busy"
                            @click="close"
                        >
                            <X aria-hidden="true" />
                        </button>
                    </header>

                    <form class="admin-action-dialog__form" novalidate @submit.prevent="emit('confirm')">
                        <div class="admin-action-dialog__body">
                            <slot />
                        </div>
                        <footer class="admin-action-dialog__footer">
                            <button
                                type="button"
                                class="admin-button admin-button--secondary"
                                :disabled="busy"
                                @click="close"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                class="admin-button"
                                :class="`admin-button--${tone}`"
                                :disabled="busy"
                            >
                                <LoaderCircle v-if="busy" class="admin-spin" aria-hidden="true" />
                                {{ busy ? 'Saving securely...' : confirmLabel }}
                            </button>
                        </footer>
                    </form>
                </section>
            </div>
        </Transition>
    </Teleport>
</template>
