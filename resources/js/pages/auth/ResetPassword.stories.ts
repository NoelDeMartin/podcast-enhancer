import type { Meta, StoryObj } from '@storybook/vue3-vite';

import ResetPassword from './ResetPassword.vue';

const meta = {
    title: 'Pages/Auth/ResetPassword',
    component: ResetPassword,
    parameters: {
        layout: 'fullscreen',
    },
} satisfies Meta<typeof ResetPassword>;

export default meta;

type Story = StoryObj<typeof meta>;

const defaultArgs = {
    token: 'storybook-token',
    email: 'email@example.com',
} as any;

export const Default: Story = {
    args: defaultArgs,
    render: (args) => ({
        components: { ResetPassword },
        setup: () => ({ args }),
        template: `<ResetPassword v-bind="args" />`,
    }),
};
