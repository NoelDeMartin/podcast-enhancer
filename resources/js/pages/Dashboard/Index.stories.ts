import type { Meta, StoryObj } from '@storybook/vue3-vite';

import { feeds } from '@/testing/stubs/feeds';
import { user } from '@/testing/stubs/user';

import { setInertiaPage } from '../../../../.storybook/mocks/inertia';
import Index from './Index.vue';

type DashboardArgs = {
    feeds: any[];
    filters: {
        search?: string;
    };
    can?: {
        uploadFiles: boolean;
    };
};

const meta = {
    title: 'Pages/Dashboard',
    component: Index,
    parameters: {
        layout: 'fullscreen',
        inertia: {
            props: {
                auth: {
                    user,
                },
            },
        },
    },
    argTypes: {
        plan: {
            control: 'select',
            options: ['basic', 'pro'],
            description: 'The subscription plan of the logged in user',
        },
    },
    args: {
        plan: 'basic',
    },
    decorators: [
        (story, { args }) => {
            setInertiaPage({
                props: {
                    auth: {
                        user: {
                            plan: args.plan,
                        },
                    },
                },
            });

            return story();
        },
    ],
    render: (args) => ({
        components: { Index },
        setup: () => {
            const { plan, ...otherArgs } = args;

            const can = {
                createManual: plan === 'pro',
                uploadFiles: plan === 'pro',
            };

            return { otherArgs, can };
        },
        template: '<Index v-bind="otherArgs" :can="can" />',
    }),
} satisfies Meta<DashboardArgs & { plan: 'basic' | 'pro' }>;

export default meta;

type Story = StoryObj<typeof meta>;

export const Default: Story = {
    args: {
        feeds,
        filters: {
            search: '',
        },
    },
};

export const Empty: Story = {
    args: {
        feeds: [],
        filters: {
            search: '',
        },
    },
};

export const Searching: Story = {
    args: {
        feeds: [feeds[0]],
        filters: {
            search: 'Laravel',
        },
    },
};

export const Syncing: Story = {
    args: {
        ...Default.args,
        feeds: [
            {
                ...feeds[0],
                latest_job_batch: {
                    job_batch: {
                        finished_at: null,
                        cancelled_at: null,
                    },
                },
            },
            ...feeds.slice(1),
        ],
    },
};

export const ProPlan: Story = {
    args: {
        ...Default.args,
        plan: 'pro',
    },
};
