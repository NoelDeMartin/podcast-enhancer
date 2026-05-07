import { showModal } from '@noeldemartin/vue-modals';
import type { Meta, StoryObj } from '@storybook/vue3-vite';
import { onMounted } from 'vue';

import { ModalsPortal } from '@/components/ui/modal';
import { entries } from '@/testing/stubs/entries';
import { feeds } from '@/testing/stubs/feeds';

import EditEntryModal from './EditEntryModal.vue';

type EditEntryModalStoryArgs = {
    feed: any;
    entry: any;
    canUploadFiles?: boolean;
};

const meta: Meta<EditEntryModalStoryArgs> = {
    title: 'Modals/EditEntryModal',
    component: EditEntryModal,
    render: (args) => ({
        components: { ModalsPortal },
        setup() {
            onMounted(() => {
                void showModal(EditEntryModal, args);
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
        entry: entries[0],
        canUploadFiles: true,
    },
};
