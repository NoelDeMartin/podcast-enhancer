import { defineComponent, h } from 'vue';

// @ts-expect-error Storybook-only shim imports runtime bundle directly.
import * as inertia from '../../node_modules/@inertiajs/vue3/dist/index.esm.js';

// @ts-expect-error Storybook-only shim imports runtime bundle directly.
export * from '../../node_modules/@inertiajs/vue3/dist/index.esm.js';

export const Head = defineComponent({ render: () => h('div', { style: 'display: none;' }) });

export const usePage = () => ({
    props: { auth: { user: null } },
    url: '/',
    version: null,
});

export default {
    ...inertia,
    Head,
    usePage,
};
