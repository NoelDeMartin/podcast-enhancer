import { showModal } from '@noeldemartin/vue-modals';
import type { Meta, StoryObj } from '@storybook/vue3-vite';
import { onMounted } from 'vue';

import { ModalsPortal } from '@/components/ui/modal';
import { feeds } from '@/testing/stubs/feeds';

import EditFeedModal from './EditFeedModal.vue';

type EditFeedModalStoryArgs = {
    feed: any;
    canUploadFiles?: boolean;
};

const meta: Meta<EditFeedModalStoryArgs> = {
    title: 'Modals/EditFeedModal',
    component: EditFeedModal,
    render: (args) => ({
        components: { ModalsPortal },
        setup() {
            onMounted(() => {
                void showModal(EditFeedModal, args);
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
        feed: feeds[2], // Manual feed
        canUploadFiles: true,
    },
};
