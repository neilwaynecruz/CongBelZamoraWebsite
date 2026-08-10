<script setup lang="ts">
import { X } from '@lucide/vue';
import { nextTick, onBeforeUnmount, ref, watch } from 'vue';
import AdminSidebar from './AdminSidebar.vue';
import type { AdminConsoleContext } from '@/types/admin-console';

const props = defineProps<{
    open: boolean;
    context: AdminConsoleContext;
}>();

const emit = defineEmits<{ close: [] }>();
const panel = ref<HTMLElement | null>(null);
const closeButton = ref<HTMLButtonElement | null>(null);
let priorFocus: HTMLElement | null = null;

const focusableSelector =
    'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';

const onKeydown = (event: KeyboardEvent) => {
    if (!props.open) {
return;
}

    if (event.key === 'Escape') {
        event.preventDefault();
        emit('close');

        return;
    }

    if (event.key !== 'Tab' || !panel.value) {
return;
}

    const focusable = Array.from(
        panel.value.querySelectorAll<HTMLElement>(focusableSelector),
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
            closeButton.value?.focus();
        } else {
            document.body.style.overflow = '';
            document.removeEventListener('keydown', onKeydown);
            priorFocus?.focus();
        }
    },
);

onBeforeUnmount(() => {
    document.body.style.overflow = '';
    document.removeEventListener('keydown', onKeydown);
});
</script>

<template>
    <Teleport to="body">
        <Transition name="admin-drawer">
            <div v-if="open" class="admin-mobile-nav" role="presentation">
                <button
                    type="button"
                    class="admin-mobile-nav__backdrop"
                    aria-label="Close navigation"
                    @click="$emit('close')"
                ></button>
                <section
                    ref="panel"
                    class="admin-mobile-nav__panel"
                    role="dialog"
                    aria-modal="true"
                    aria-label="Admin navigation"
                >
                    <button
                        ref="closeButton"
                        type="button"
                        class="admin-icon-button admin-mobile-nav__close"
                        aria-label="Close navigation"
                        @click="$emit('close')"
                    >
                        <X aria-hidden="true" />
                    </button>
                    <AdminSidebar
                        :context="context"
                        mobile
                        @navigate="$emit('close')"
                    />
                </section>
            </div>
        </Transition>
    </Teleport>
</template>
