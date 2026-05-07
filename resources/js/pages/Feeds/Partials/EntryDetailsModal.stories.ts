import { showModal } from '@noeldemartin/vue-modals';
import type { Meta, StoryObj } from '@storybook/vue3-vite';
import { onMounted } from 'vue';

import { ModalsPortal } from '@/components/ui/modal';
import { entries } from '@/testing/stubs/entries';

import EntryDetailsModal from './EntryDetailsModal.vue';

type EntryDetailsModalStoryArgs = {
    entry: any;
};

const meta: Meta<EntryDetailsModalStoryArgs> = {
    title: 'Modals/EntryDetailsModal',
    component: EntryDetailsModal,
    render: (args) => ({
        components: { ModalsPortal },
        setup() {
            onMounted(() => {
                void showModal(EntryDetailsModal, args);
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
        entry: entries[0],
    },
};

export const Minimal: Story = {
    args: {
        entry: entries[1],
    },
};
