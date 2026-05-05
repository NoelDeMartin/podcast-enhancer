import type { Meta, StoryObj } from '@storybook/vue3-vite';

import TwoFactorChallenge from './TwoFactorChallenge.vue';

const meta: Meta = {
    title: 'Pages/Auth/TwoFactorChallenge',
    component: TwoFactorChallenge,
    parameters: {
        layout: 'fullscreen',
    },
};

export default meta;

type Story = StoryObj<typeof meta>;

export const Default: Story = {};
