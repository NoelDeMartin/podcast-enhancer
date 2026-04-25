import type { Meta, StoryObj } from '@storybook/vue3-vite';

import { Badge } from './index';

type BadgeStoryArgs = {
    variant: 'default' | 'secondary' | 'destructive' | 'outline';
};

const meta: Meta<BadgeStoryArgs> = {
    title: 'UI/Badge',
    component: Badge,
    tags: ['autodocs'],
    argTypes: {
        variant: {
            control: 'select',
            options: ['default', 'secondary', 'destructive', 'outline'],
        },
    },
    args: {
        variant: 'default',
    },
};

export default meta;
type Story = StoryObj<typeof meta>;

export const Default: Story = {
    render: (args) => ({
        components: { Badge },
        setup: () => ({ args }),
        template: '<Badge v-bind="args">Badge</Badge>',
    }),
};

export const Secondary: Story = {
    args: {
        variant: 'secondary',
    },
    render: (args) => ({
        components: { Badge },
        setup: () => ({ args }),
        template: '<Badge v-bind="args">Secondary</Badge>',
    }),
};

export const Destructive: Story = {
    args: {
        variant: 'destructive',
    },
    render: (args) => ({
        components: { Badge },
        setup: () => ({ args }),
        template: '<Badge v-bind="args">Destructive</Badge>',
    }),
};

export const Outline: Story = {
    args: {
        variant: 'outline',
    },
    render: (args) => ({
        components: { Badge },
        setup: () => ({ args }),
        template: '<Badge v-bind="args">Outline</Badge>',
    }),
};
