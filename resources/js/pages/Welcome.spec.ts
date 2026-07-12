import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';
import Welcome from '@/pages/Welcome.vue';

describe('Welcome page', () => {
    it('renders the development baseline heading', () => {
        const wrapper = mount(Welcome);

        expect(wrapper.text()).toContain('Bel Zamora District Office');
        expect(wrapper.text()).toContain('Development environment');
    });
});
