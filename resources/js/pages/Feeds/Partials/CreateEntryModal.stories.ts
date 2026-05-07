import { showModal } from '@noeldemartin/vue-modals';
import type { Meta, StoryObj } from '@storybook/vue3-vite';
import { onMounted } from 'vue';

import { ModalsPortal } from '@/components/ui/modal';
import { feeds } from '@/testing/stubs/feeds';

import CreateEntryModal from './CreateEntryModal.vue';

type CreateEntryModalStoryArgs = {
    feed: any;
    canUploadFiles?: boolean;
};

const meta: Meta<CreateEntryModalStoryArgs> = {
    title: 'Modals/CreateEntryModal',
    component: CreateEntryModal,
    render: (args) => ({
        components: { ModalsPortal },
        setup() {
            onMounted(() => {
                void showModal(CreateEntryModal, args);
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
        feed: feeds[0],
        canUploadFiles: true,
    },
};

export const NoUpload: Story = {
    args: {
        feed: feeds[0],
        canUploadFiles: false,
    },
};
