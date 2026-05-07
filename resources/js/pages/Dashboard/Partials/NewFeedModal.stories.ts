import { showModal } from '@noeldemartin/vue-modals';
import type { Meta, StoryObj } from '@storybook/vue3-vite';
import { onMounted } from 'vue';

import { ModalsPortal } from '@/components/ui/modal';

import NewFeedModal from './NewFeedModal.vue';

type NewFeedModalStoryArgs = {
    canCreateManual?: boolean;
    canUploadFiles?: boolean;
};

const meta: Meta<NewFeedModalStoryArgs> = {
    title: 'Modals/NewFeedModal',
    component: NewFeedModal,
    render: (args) => ({
        components: { ModalsPortal },
        setup() {
            onMounted(() => {
                void showModal(NewFeedModal, args);
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
        canCreateManual: true,
        canUploadFiles: true,
    },
};

export const Restricted: Story = {
    args: {
        canCreateManual: false,
        canUploadFiles: false,
    },
};
