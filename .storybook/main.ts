import { fileURLToPath, URL } from 'node:url';

import type { StorybookConfig } from '@storybook/vue3-vite';
import { mergeConfig } from 'vite';

const config: StorybookConfig = {
    framework: '@storybook/vue3-vite',
    stories: [
        '../resources/js/components/**/*.stories.@(ts|tsx)',
        '../resources/js/pages/**/*.stories.@(ts|tsx)',
    ],
    core: { disableTelemetry: true },
    features: { sidebarOnboardingChecklist: false },
    viteFinal: async (config) =>
        mergeConfig(config, {
            base: process.env.NODE_ENV === 'production' ? '/podcast-enhancer/' : '/',
            resolve: {
                alias: {
                    '@inertiajs/vue3': fileURLToPath(
                        new URL('./mocks/inertia.ts', import.meta.url),
                    ),
                },
            },
        }),
};

export default config;
