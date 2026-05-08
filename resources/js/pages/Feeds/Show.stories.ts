import type { Meta, StoryObj } from '@storybook/vue3-vite';

import { entries } from '@/testing/stubs/entries';
import { feeds } from '@/testing/stubs/feeds';
import { user } from '@/testing/stubs/user';

import { setInertiaPage } from '../../../../.storybook/mocks/inertia';
import Show from './Show.vue';

type ShowArgs = {
    feed: any;
    entries: {
        data: any[];
        links: any[];
    };
    filters: {
        search?: string;
    };
    can: {
        update: boolean;
        delete: boolean;
        sync: boolean;
        uploadFiles: boolean;
    };
    isGuest?: boolean;
};

const meta = {
    title: 'Pages/Feed',
    component: Show,
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
    decorators: [
        (story, { args }) => {
            setInertiaPage({
                props: {
                    auth: {
                        user: args.isGuest ? null : user,
                    },
                },
            });

            return story();
        },
    ],
    render: (args) => ({
        components: { Show },
        setup: () => {
            const { isGuest: _, ...otherArgs } = args;
            return { otherArgs };
        },
        template: '<Show v-bind="otherArgs" />',
    }),
} satisfies Meta<ShowArgs>;

export default meta;

type Story = StoryObj<typeof meta>;

const commonLinks = [
    { url: null, label: '&laquo; Previous', active: false },
    { url: '/feeds/1?page=1', label: '1', active: true },
    { url: null, label: 'Next &raquo;', active: false },
];

export const Default: Story = {
    args: {
        feed: feeds[0],
        entries: {
            data: entries,
            links: commonLinks,
        },
        filters: {
            search: '',
        },
        can: {
            update: true,
            delete: true,
            sync: true,
            uploadFiles: true,
        },
    },
};

export const Empty: Story = {
    args: {
        ...Default.args,
        entries: {
            data: [],
            links: [],
        },
    },
};

export const Guest: Story = {
    args: {
        ...Default.args,
        isGuest: true,
        can: {
            update: false,
            delete: false,
            sync: false,
            uploadFiles: false,
        },
    },
};

export const Syncing: Story = {
    args: {
        ...Default.args,
        feed: {
            ...feeds[0],
            latest_job_batch: {
                job_batch: {
                    finished_at: null,
                    cancelled_at: null,
                },
            },
        },
    },
};

export const SyncFailed: Story = {
    args: {
        ...Default.args,
        feed: {
            ...feeds[0],
            latest_job_batch: {
                job_batch: {
                    finished_at: '2024-03-12T10:00:00Z',
                    cancelled_at: '2024-03-12T10:00:00Z',
                },
            },
        },
    },
};

export const ManualFeed: Story = {
    args: {
        ...Default.args,
        feed: feeds[2],
    },
};
