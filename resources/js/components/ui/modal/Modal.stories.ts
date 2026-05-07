import type { Meta, StoryObj } from '@storybook/vue3-vite';
import { onMounted } from 'vue';
import { showModal } from '@noeldemartin/vue-modals';
import { ModalsPortal } from '@/components/ui/modal';
import Modal from './Modal.vue';
import { Button } from '@/components/ui/button';

type ModalStoryArgs = {
    title?: string;
    description?: string;
};

const meta: Meta<ModalStoryArgs> = {
    title: 'Modals/BaseModal',
    component: Modal,
    render: (args) => ({
        components: { ModalsPortal, Button },
        setup() {
            onMounted(() => {
                void showModal({
                    component: Modal,
                    props: args,
                    slots: {
                        default: '<div class="py-4">This is the base modal content.</div><div class="flex justify-end gap-2"><Button variant="outline">Cancel</Button><Button>Confirm</Button></div>',
                    },
                } as any);
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
        title: 'Modal Title',
        description: 'This is a description of what the modal is for.',
    },
};
