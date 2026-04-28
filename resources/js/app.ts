import { createInertiaApp } from '@inertiajs/vue3';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import '../css/app.css';
import { initializeTheme } from '@/composables/useAppearance';
import { vScrollOnClick } from '@/directives/scrollOnClick';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

void createInertiaApp({
    title: (title: string) => (title ? `${title} - ${appName}` : appName),
    resolve: (name: string) => {
        const pages = import.meta.glob<DefineComponent>('./pages/**/*.vue');

        const primary = `./pages/${name}.vue`;
        const last = name.split('/').filter(Boolean).at(-1);
        const fallback = last ? `./pages/${name}/${last}.vue` : null;

        const page = pages[primary] ?? (fallback ? pages[fallback] : undefined);
        if (!page) {
            throw new Error(`Page not found: ${primary}${fallback ? ` (or ${fallback})` : ''}`);
        }

        return page();
    },
    setup({ el, App, props, plugin }: { el: Element; App: any; props: any; plugin: any }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .directive('scroll-on-click', vScrollOnClick)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on page load...
initializeTheme();
