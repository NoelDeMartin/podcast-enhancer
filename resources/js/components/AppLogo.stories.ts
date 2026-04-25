import type { Meta, StoryObj } from '@storybook/vue3-vite';

import AppLogo from './AppLogo.vue';

const meta = {
    title: 'Components/AppLogo',
    component: AppLogo,
    tags: ['autodocs'],
} satisfies Meta<typeof AppLogo>;

export default meta;
type Story = StoryObj<typeof meta>;

export const Default: Story = {
    render: () => ({
        components: { AppLogo },
        template: '<div class="flex items-center gap-2 p-6"><AppLogo /></div>',
    }),
};
