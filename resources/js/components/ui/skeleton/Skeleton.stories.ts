import type { Meta, StoryObj } from '@storybook/vue3-vite';

import { Skeleton } from './index';

const meta = {
    title: 'UI/Skeleton',
    component: Skeleton,
    tags: ['autodocs'],
} satisfies Meta<typeof Skeleton>;

export default meta;
type Story = StoryObj<typeof meta>;

export const Default: Story = {
    render: () => ({
        components: { Skeleton },
        template: `
          <div class="flex items-center space-x-4 p-6">
            <Skeleton class="h-12 w-12 rounded-full" />
            <div class="space-y-2">
              <Skeleton class="h-4 w-[250px]" />
              <Skeleton class="h-4 w-[200px]" />
            </div>
          </div>
        `,
    }),
};

export const Card: Story = {
    render: () => ({
        components: { Skeleton },
        template: `
          <div class="flex flex-col space-y-3 p-6">
            <Skeleton class="h-[125px] w-[250px] rounded-xl" />
            <div class="space-y-2">
              <Skeleton class="h-4 w-[250px]" />
              <Skeleton class="h-4 w-[200px]" />
            </div>
          </div>
        `,
    }),
};
