import type { StorybookConfig } from '@storybook/vue3-vite';

const config: StorybookConfig = {
    framework: '@storybook/vue3-vite',
    stories: ['../resources/js/components/**/*.stories.@(ts|tsx)'],
    core: { disableTelemetry: true },
};

export default config;
