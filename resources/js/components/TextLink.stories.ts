import type { Meta, StoryObj } from '@storybook/vue3-vite';

import TextLink from './TextLink.vue';

type TextLinkStoryArgs = {
    href: string;
};

const meta: Meta<TextLinkStoryArgs> = {
    title: 'UI/TextLink',
    component: TextLink,
    tags: ['autodocs'],
    argTypes: {
        href: { control: 'text' },
    },
    args: {
        href: '#',
    },
};

export default meta;
type Story = StoryObj<typeof meta>;

export const Default: Story = {
    render: (args) => ({
        components: { TextLink },
        setup: () => ({ args }),
        template: `
          <div class="p-6">
            <TextLink v-bind="args">Click here to go somewhere</TextLink>
          </div>
        `,
    }),
};
