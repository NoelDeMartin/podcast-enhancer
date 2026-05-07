import type { Meta, StoryObj } from '@storybook/vue3-vite';

import Login from './Login.vue';

const meta: Meta = {
    title: 'Pages/Auth/Login',
    component: Login,
    parameters: {
        layout: 'fullscreen',
    },
    args: {
        canResetPassword: true,
    },
};

export default meta;

type Story = StoryObj<typeof meta>;

export const Default: Story = {};
