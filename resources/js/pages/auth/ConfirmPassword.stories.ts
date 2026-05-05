import type { Meta, StoryObj } from '@storybook/vue3-vite';

import ConfirmPassword from './ConfirmPassword.vue';

const meta: Meta = {
    title: 'Pages/Auth/ConfirmPassword',
    component: ConfirmPassword,
    parameters: {
        layout: 'fullscreen',
    },
};

export default meta;

type Story = StoryObj<typeof meta>;

export const Default: Story = {};
