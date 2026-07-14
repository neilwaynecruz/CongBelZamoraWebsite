import { vi } from 'vitest';
import { defineComponent } from 'vue';

vi.mock('@inertiajs/vue3', () => ({
    Head: defineComponent({
        name: 'InertiaHead',
        props: {
            title: {
                type: String,
                default: undefined,
            },
        },
        template: '<div><slot /></div>',
    }),
    Link: defineComponent({
        name: 'InertiaLink',
        props: {
            href: {
                type: String,
                required: true,
            },
        },
        template: '<a :href="href"><slot /></a>',
    }),
    usePage: () => ({
        url: '/',
        props: {},
    }),
}));
