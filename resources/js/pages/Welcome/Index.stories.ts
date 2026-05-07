import type { Meta, StoryObj } from '@storybook/vue3-vite';

import { user } from '@/testing/stubs/user';

import Index from './Index.vue';

const meta: Meta = {
    title: 'Pages/Welcome',
    component: Index,
    parameters: {
        layout: 'fullscreen',
    },
};

export default meta;

type Story = StoryObj<typeof meta>;

export const Default: Story = {};

export const LoggedIn: Story = {
    parameters: {
        inertia: {
            props: {
                auth: {
                    user,
                },
            },
        },
    },
};
