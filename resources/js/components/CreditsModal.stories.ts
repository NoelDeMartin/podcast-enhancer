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
};

const meta: Meta<NewFeedModalStoryArgs> = {
    title: 'Modals/CreditsModal',
    component: CreditsModal,
    parameters: {
        inertia: {
            props: {
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
            window.fetch = ((url: string) => {
                if (url.includes('/credits-usage')) {
                    return Promise.resolve({
                        ok: true,
                        json: () => Promise.resolve(args.response),
                    });
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
        response: {
            usages: Array.from({ length: 50 }, (_, i) => ({
                id: i + 1,
                created_at: new Date(2026, 4, 8 - i).toISOString(),
                credits: Math.floor(Math.random() * 150) + 1,
                entry: {
                    name:
                        i % 10 === 0
                            ? `Episode ${i + 1}: This is an extremely long episode name that should definitely break the UI if it is not handled correctly with truncation or wrapping`
                            : `Episode ${i + 1}: Standard podcast episode title`,
                },
            })),
            current_credits: 100,
        },
    },
};

export const Empty: Story = {
    args: {
        credits: 0,
        response: {
            usages: [],
            current_credits: 0,
        },
    },
};

export const ProcessingFeedback: Story = {
    args: {
        credits: 42,
        response: {
            usages: [
                {
                    id: 1,
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
