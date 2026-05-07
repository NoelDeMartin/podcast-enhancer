import { showModal } from '@noeldemartin/vue-modals';
import type { Meta, StoryObj } from '@storybook/vue3-vite';
import { onMounted } from 'vue';

import { ModalsPortal } from '@/components/ui/modal';
import { entries } from '@/testing/stubs/entries';

import EntryFailureModal from './EntryFailureModal.vue';

type EntryFailureModalStoryArgs = {
    entry: any;
};

const meta: Meta<EntryFailureModalStoryArgs> = {
    title: 'Modals/EntryFailureModal',
    component: EntryFailureModal,
    render: (args) => ({
        components: { ModalsPortal },
        setup() {
            onMounted(() => {
                void showModal(EntryFailureModal, args);
            });
            return {};
        },
        template: '<ModalsPortal />',
    }),
};

export default meta;
type Story = StoryObj<typeof meta>;

export const Default: Story = {
    args: {
        entry: {
            ...entries[0],
            latest_job_batch: {
                job_batch: {
                    failed_job_details: [
                        { exception: 'Something went wrong during transcription.' },
                    ],
                },
            },
        },
    },
};
