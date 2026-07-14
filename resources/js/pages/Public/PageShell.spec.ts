import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';
import PageShell from '@/pages/Public/PageShell.vue';

describe('Public page shell', () => {
    it('renders sitemap route shell content with breadcrumbs', () => {
        const wrapper = mount(PageShell, {
            props: {
                breadcrumbs: [
                    { title: 'Home', href: '/' },
                    { title: 'Programs' },
                ],
                description: 'Approved page description.',
                eyebrow: 'Public-service directory',
                sections: [
                    {
                        body: 'Section detail.',
                        title: 'Section title',
                    },
                ],
                title: 'Programs and Services',
            },
        });

        expect(wrapper.text()).toContain('Programs and Services');
        expect(wrapper.text()).toContain('Approved page description.');
        expect(wrapper.text()).toContain('Section title');
        expect(wrapper.find('nav[aria-label="Breadcrumb"]').exists()).toBe(
            true,
        );
    });
});
