import { showModal } from '@noeldemartin/vue-modals';
import type { Meta, StoryObj } from '@storybook/vue3-vite';
import { onMounted } from 'vue';

import { ModalsPortal } from '@/components/ui/modal';
import { user as userStub } from '@/testing/stubs/user';

import { setInertiaPage } from '../../../.storybook/mocks/inertia';
import CreditsModal from './CreditsModal.vue';

type NewFeedModalStoryArgs = {
    credits?: number;
    response?: any;
    delay?: number;
};

const generateResponse = (page: number) => ({
    usages: {
        data: Array.from({ length: 8 }, (_, i) => {
            const isUsage = (i + (page - 1) * 8) % 3 !== 0;
            return {
                id: (page - 1) * 8 + i + 1,
                created_at: new Date(2026, 4, 8 - ((page - 1) * 8 + i)).toISOString(),
                credits: Math.floor(Math.random() * 150) + 1,
                type: isUsage ? 'usage' : 'topup',
                description: isUsage ? undefined : `Top-up transaction ${(page - 1) * 8 + i + 1}`,
                entry: isUsage
                    ? {
                          name:
                              i === 0 && page === 1
                                  ? `Episode ${(page - 1) * 8 + i + 1}: This is an extremely long episode name that should definitely break the UI if it is not handled correctly with truncation or wrapping`
                                  : `Episode ${(page - 1) * 8 + i + 1}: Standard podcast episode title`,
                      }
                    : undefined,
            };
        }),
        links: [
            {
                url: page > 1 ? `/credits?page=${page - 1}` : null,
                label: '&larr; Previous',
                active: false,
            },
            { url: '/credits?page=1', label: '1', active: page === 1 },
            { url: '/credits?page=2', label: '2', active: page === 2 },
            { url: '/credits?page=3', label: '3', active: page === 3 },
            {
                url: page < 3 ? `/credits?page=${page + 1}` : null,
                label: 'Next &rarr;',
                active: false,
            },
        ],
        path: '/credits',
        current_page: page,
    },
    current_credits: 100,
});

const meta: Meta<NewFeedModalStoryArgs> = {
    title: 'Modals/CreditsModal',
    component: CreditsModal,
    args: {
        delay: 1000,
    },
    parameters: {
        inertia: {
            props: {
                appUrl: 'http://localhost',
                auth: {
                    user: {
                        ...userStub,
                        credits: 100,
                    },
                },
            },
        },
    },
    decorators: [
        (story, { args }) => {
            setInertiaPage({
                props: {
                    auth: {
                        user: {
                            ...userStub,
                            credits: args.credits,
                        },
                    },
                },
            });

            // Mock fetch
            window.fetch = (async (url: string) => {
                if (url.includes('/credits')) {
                    const pageMatch = url.match(/page=(\d+)/);
                    const page = pageMatch ? parseInt(pageMatch[1]) : 1;

                    if (args.delay) {
                        await new Promise((resolve) => setTimeout(resolve, args.delay));
                    }

                    return {
                        ok: true,
                        json: () => Promise.resolve(args.response || generateResponse(page)),
                    };
                }
                return Promise.reject(new Error('Unknown URL'));
            }) as any;

            return story();
        },
    ],
    render: () => ({
        components: { ModalsPortal },
        setup() {
            onMounted(() => {
                void showModal(CreditsModal);
            });
            return {};
        },
        template: '<ModalsPortal />',
    }),
};

export default meta;
type Story = StoryObj<typeof meta>;

export const Many: Story = {
    args: {
        credits: 100,
    },
};

export const Empty: Story = {
    args: {
        credits: 0,
        response: {
            usages: {
                data: [],
                links: [],
            },
            current_credits: 0,
        },
    },
};

export const ProcessingFeedback: Story = {
    args: {
        credits: 42,
        response: {
            usages: {
                data: [
                    {
                        id: 1,
                        type: 'usage',
                        created_at: new Date(2026, 4, 8).toISOString(),
                        credits: 12,
                        entry: {
                            id: 1,
                            name: 'Episode 1: Failed processing should show an icon button',
                            slug: 'episode-1-failed-processing',
                            latest_job_batch: {
                                job_batch: {
                                    id: 'test-batch-id',
                                    finished_at: null,
                                    cancelled_at: Date.now(),
                                    failed_job_details: [
                                        {
                                            exception: 'Something went wrong.',
                                        },
                                    ],
                                },
                            },
                        },
                    },
                    {
                        id: 2,
                        type: 'usage',
                        created_at: new Date(2026, 4, 8, 1).toISOString(),
                        credits: 7,
                        entry: {
                            id: 2,
                            name: 'Episode 2: Still processing should show a spinner',
                            slug: 'episode-2-still-processing',
                            latest_job_batch: {
                                job_batch: {
                                    id: 'test-batch-id-2',
                                    finished_at: null,
                                    cancelled_at: null,
                                },
                            },
                        },
                    },
                ],
                links: [],
            },
            current_credits: 42,
        },
    },
};

export const Loading: Story = {
    decorators: [
        (story) => {
            window.fetch = (() => new Promise(() => {})) as any;
            return story();
        },
    ],
};
