import type { Meta, StoryObj } from '@storybook/vue3-vite';

import { entries } from '@/testing/stubs/entries';
import { feeds } from '@/testing/stubs/feeds';
import { user } from '@/testing/stubs/user';

import { setInertiaPage } from '../../../../.storybook/mocks/inertia';
import Show from './Show.vue';

type ShowArgs = {
    entry: any;
    can: {
        update: boolean;
        delete: boolean;
        uploadFiles: boolean;
    };
    isGuest?: boolean;
};

const meta = {
    title: 'Pages/Entry',
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

const baseEntry = {
    ...entries[0],
    feed: feeds[0],
    audio_url: entries[0].absolute_audio_url,
    can: {
        produce: true,
        regenerate: true,
    },
    chapters: [
        { startTime: 0, title: 'Introduction' },
        { startTime: 300, title: 'New Directory Structure' },
        { startTime: 900, title: 'Conclusion' },
    ],
    transcription: JSON.stringify([
        { start_seconds: 3, speaker: 'Host', text: 'Welcome back to the Laravel Podcast.' },
        { start_seconds: 42, speaker: 'Guest', text: 'Today we’re talking about what’s new.' },
    ]),
    transcription_path: 'transcriptions/1.json',
};

export const Default: Story = {
    args: {
        entry: baseEntry,
        can: {
            update: true,
            delete: true,
            uploadFiles: true,
        },
    },
};

export const Guest: Story = {
    args: {
        ...Default.args,
        isGuest: true,
        entry: {
            ...(Default.args as any).entry,
            can: {
                produce: false,
                regenerate: false,
            },
        },
        can: {
            update: false,
            delete: false,
            uploadFiles: false,
        },
    },
};

export const NoAudio: Story = {
    args: {
        ...Default.args,
        entry: {
            ...baseEntry,
            absolute_audio_url: null,
            audio_url: null,
        },
    },
};

export const NoEnhancements: Story = {
    args: {
        ...Default.args,
        entry: {
            ...baseEntry,
            summary: null,
            chapters: [],
            transcription: null,
            transcription_path: null,
        },
    },
};

export const Processing: Story = {
    args: {
        ...Default.args,
        entry: {
            ...baseEntry,
            latest_job_batch: {
                job_batch: {
                    finished_at: null,
                    cancelled_at: null,
                },
            },
        },
    },
};

export const Failed: Story = {
    args: {
        ...Default.args,
        entry: {
            ...baseEntry,
            latest_job_batch: {
                job_batch: {
                    finished_at: '2024-03-12T10:00:00Z',
                    cancelled_at: '2024-03-12T10:00:00Z',
                    failed_job_details: [
                        {
                            exception:
                                'RuntimeException: Something went wrong during transcription.',
                        },
                    ],
                },
            },
        },
    },
};
