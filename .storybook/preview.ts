import '../resources/css/app.css';

import type { Preview } from '@storybook/vue3-vite';
import { setup } from '@storybook/vue3-vite';
import { vScrollOnClick } from '../resources/js/directives/scrollOnClick';

setup((app) => {
    app.directive('scroll-on-click', vScrollOnClick);
});

const preview: Preview = {};

export default preview;
