import { showModal } from '@noeldemartin/vue-modals';
import type { Meta, StoryObj } from '@storybook/vue3-vite';
import { onMounted } from 'vue';

import { ModalsPortal } from '@/components/ui/modal';
import { feeds } from '@/testing/stubs/feeds';

import SyncFailureModal from './SyncFailureModal.vue';

type SyncFailureModalStoryArgs = {
    feed: any;
};

const meta: Meta<SyncFailureModalStoryArgs> = {
    title: 'Modals/SyncFailureModal',
    component: SyncFailureModal,
    render: (args) => ({
        components: { ModalsPortal },
        setup() {
            onMounted(() => {
                void showModal(SyncFailureModal, args);
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
        feed: {
            ...feeds[0],
            latest_job_batch: {
                job_batch: {
                    failed_job_details: [
                        { exception: 'Something went wrong during synchronization.' },
                    ],
                },
            },
        },
    },
};
