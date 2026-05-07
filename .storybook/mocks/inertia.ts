import { reactive, defineComponent, h } from 'vue';

// @ts-expect-error Storybook-only shim imports runtime bundle directly.
import * as inertia from '../../node_modules/@inertiajs/vue3/dist/index.esm.js';

// @ts-expect-error Storybook-only shim imports runtime bundle directly.
export * from '../../node_modules/@inertiajs/vue3/dist/index.esm.js';

export const Head = defineComponent({ render: () => h('div', { style: 'display: none;' }) });

const page = reactive<{
    props: Record<string, unknown> & {
        auth: {
            user: Record<string, unknown> | null;
        };
    };
    url: string;
    version: unknown;
}>({
    props: { auth: { user: null } },
    url: '/',
    version: null,
});

export const usePage = () => page;

export const setInertiaPage = (data: any) => {
    if (data.props) {
        if (data.props.auth && data.props.auth.user) {
            page.props.auth = page.props.auth || { user: null };
            page.props.auth.user = {
                ...((page.props.auth.user ?? {}) as Record<string, unknown>),
                ...(data.props.auth.user as Record<string, unknown>),
            };
        }

        Object.assign(page.props, {
            ...data.props,
            auth: page.props.auth,
        });
    }
    if (data.url !== undefined) {
        page.url = data.url;
    }
    if (data.version !== undefined) {
        page.version = data.version;
    }
};

export default {
    ...inertia,
    Head,
    usePage,
};
