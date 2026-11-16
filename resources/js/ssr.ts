import { createInertiaApp } from '@inertiajs/vue3';
import createServer from '@inertiajs/vue3/server';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { App, DefineComponent } from 'vue';
import { createSSRApp, h } from 'vue';
import { renderToString } from 'vue/server-renderer';

import { ModalsPortal } from '@/components/ui/modal';
import { vScrollOnClick } from '@/directives/scrollOnClick';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createServer(
    (page) => {
        let pageApp: App | null = null;
        let pageHtml = '';

        /**
         * Inertia renders the page app as `innerHTML: render(pageApp)` inside a root app, relying on
         * Vue's SSR buffer to await the returned Promise. Since Vue 3.5.45, `innerHTML` is stringified,
         * which outputs "[object Promise]". To work around it, the page app is rendered before the root
         * app, and its markup is returned synchronously when Inertia asks for it.
         */
        const render = ((app: App) => {
            if (app === pageApp) {
                return pageHtml;
            }

            if (!pageApp) {
                return renderToString(app);
            }

            return renderToString(pageApp).then((html) => {
                pageHtml = html;

                return renderToString(app);
            });
        }) as typeof renderToString;

        return createInertiaApp({
            page,
            render,
            title: (title) => (title ? `${title} - ${appName}` : appName),
            resolve: (name) =>
                resolvePageComponent(
                    `./pages/${name}.vue`,
                    import.meta.glob<DefineComponent>('./pages/**/*.vue'),
                ),
            setup: ({ App, props, plugin }) =>
                (pageApp = createSSRApp({
                    render: () => h('div', [h(App, props), h(ModalsPortal)]),
                })
                    .use(plugin)
                    .directive('scroll-on-click', vScrollOnClick)),
        });
    },
    { cluster: true },
);
