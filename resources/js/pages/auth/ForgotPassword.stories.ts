import type { Meta, StoryObj } from '@storybook/vue3-vite';

import ForgotPassword from './ForgotPassword.vue';

const meta = {
    title: 'Pages/Auth/ForgotPassword',
    component: ForgotPassword,
    parameters: {
        layout: 'fullscreen',
    },
} satisfies Meta<typeof ForgotPassword>;

export default meta;

type Story = StoryObj<typeof meta>;

export const Default: Story = {};

export const WithStatus: Story = {
    render: () => ({
        components: { ForgotPassword },
        template: `<ForgotPassword status="If this email exists, we sent a reset link." />`,
    }),
};
