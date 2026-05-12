import '../resources/css/app.css';
import type { Preview } from '@storybook/vue3-vite';
import { setup } from '@storybook/vue3-vite';

import { vScrollOnClick } from '../resources/js/directives/scrollOnClick';
import { setInertiaPage } from './mocks/inertia';

setup((app) => {
    app.directive('scroll-on-click', vScrollOnClick);
});

const preview: Preview = {
    decorators: [
        (story, { parameters }) => {
            if (parameters.inertia) {
                setInertiaPage(parameters.inertia);
            } else {
                setInertiaPage({
                    props: { appUrl: 'http://localhost', auth: { user: null } },
                    url: '/',
                    version: null,
                });
            }
            return story();
        },
    ],
};

export default preview;
