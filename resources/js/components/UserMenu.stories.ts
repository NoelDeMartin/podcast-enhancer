import type { Meta, StoryObj } from '@storybook/vue3-vite';

import { setInertiaPage } from '../../../.storybook/mocks/inertia';
import UserMenu from './UserMenu.vue';

const meta: Meta<typeof UserMenu> = {
    title: 'UI/UserMenu',
    component: UserMenu,
    parameters: {
        layout: 'centered',
    },
    decorators: [
        (story) => {
            return {
                components: { story },
                template:
                    '<div class="flex items-center justify-center p-12 bg-neo-bg min-h-[200px]"><story /></div>',
            };
        },
    ],
};

export default meta;

type Story = StoryObj<typeof meta>;

export const Default: Story = {
    args: {},
    play: () => {
        setInertiaPage({
            props: {
                auth: {
                    user: {
                        name: 'John Doe',
                        email: 'john@example.com',
                        avatar: 'https://github.com/shadcn.png',
                        plan: 'basic',
                    },
                },
            },
        });
    },
};

export const ProPlan: Story = {
    args: {},
    play: () => {
        setInertiaPage({
            props: {
                auth: {
                    user: {
                        name: 'Jane Pro',
                        email: 'jane@example.com',
                        avatar: 'https://github.com/shadcn.png',
                        plan: 'pro',
                    },
                },
            },
        });
    },
};

export const NoAvatar: Story = {
    args: {},
    play: () => {
        setInertiaPage({
            props: {
                auth: {
                    user: {
                        name: 'No Avatar User',
                        email: 'noavatar@example.com',
                        avatar: null,
                        plan: 'basic',
                    },
                },
            },
        });
    },
};
