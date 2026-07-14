import { vi } from 'vitest';
import { defineComponent, reactive } from 'vue';

type MockFormOptions = {
    preserveScroll?: boolean;
    onSuccess?: () => void;
};

type MockForm<T extends Record<string, unknown>> = T & {
    errors: Record<string, string>;
    processing: boolean;
    post: (url: string, options?: MockFormOptions) => void;
    reset: () => void;
};

const createMockForm = <T extends Record<string, unknown>>(
    initialData: T,
): MockForm<T> => {
    const form = reactive({
        ...initialData,
        errors: {},
        processing: false,
        post: (_url: string, options?: MockFormOptions) => {
            options?.onSuccess?.();
        },
        reset: () => {},
    }) as MockForm<T>;

    form.post = vi.fn((_url: string, options?: MockFormOptions) => {
        options?.onSuccess?.();
    });
    form.reset = vi.fn(() => {
        Object.assign(form, initialData);
    });

    return form;
};

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
    useForm: createMockForm,
}));
