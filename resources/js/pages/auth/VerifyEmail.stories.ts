import type { Meta, StoryObj } from '@storybook/vue3-vite';

import VerifyEmail from './VerifyEmail.vue';

const meta = {
    title: 'Pages/Auth/VerifyEmail',
    component: VerifyEmail,
    parameters: {
        layout: 'fullscreen',
    },
} satisfies Meta<typeof VerifyEmail>;

export default meta;

type Story = StoryObj<typeof meta>;

export const Default: Story = {};

export const VerificationLinkSent: Story = {
    render: () => ({
        components: { VerifyEmail },
        template: `<VerifyEmail status="verification-link-sent" />`,
    }),
};
