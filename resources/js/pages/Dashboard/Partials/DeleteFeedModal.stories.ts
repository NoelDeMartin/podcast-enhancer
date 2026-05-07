import { showModal } from '@noeldemartin/vue-modals';
import type { Meta, StoryObj } from '@storybook/vue3-vite';
import { onMounted } from 'vue';

import { ModalsPortal } from '@/components/ui/modal';
import { feeds } from '@/testing/stubs/feeds';

import DeleteFeedModal from './DeleteFeedModal.vue';

type DeleteFeedModalStoryArgs = {
    feed: any;
};

const meta: Meta<DeleteFeedModalStoryArgs> = {
    title: 'Modals/DeleteFeedModal',
    component: DeleteFeedModal,
    render: (args) => ({
        components: { ModalsPortal },
        setup() {
            onMounted(() => {
                void showModal(DeleteFeedModal, args);
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
