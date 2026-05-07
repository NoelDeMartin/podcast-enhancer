import { showModal } from '@noeldemartin/vue-modals';
import type { Meta, StoryObj } from '@storybook/vue3-vite';
import { onMounted } from 'vue';

import { ModalsPortal } from '@/components/ui/modal';
import { feeds } from '@/testing/stubs/feeds';

import ImportEpisodesModal from './ImportEpisodesModal.vue';

type ImportEpisodesModalStoryArgs = {
    feed: any;
};

const meta: Meta<ImportEpisodesModalStoryArgs> = {
    title: 'Modals/ImportEpisodesModal',
    component: ImportEpisodesModal,
    render: (args) => ({
        components: { ModalsPortal },
        setup() {
            onMounted(() => {
                void showModal(ImportEpisodesModal, args);
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
    },
};
