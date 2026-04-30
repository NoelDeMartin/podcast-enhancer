import type { Meta, StoryObj } from '@storybook/vue3-vite';

import Welcome from './Welcome.vue';

const meta: Meta = {
    title: 'Pages/Welcome',
    component: Welcome,
    parameters: {
        layout: 'fullscreen',
    },
};

export default meta;

type Story = StoryObj<typeof meta>;

export const Default: Story = {};
