import type { Meta, StoryObj } from '@storybook/vue3-vite';

import type { ButtonVariants } from './index';
import { Button } from './index';

type ButtonStoryArgs = {
    variant?: ButtonVariants['variant'];
    size?: ButtonVariants['size'];
};

const meta: Meta<ButtonStoryArgs> = {
    title: 'UI/Button',
    component: Button,
    tags: ['autodocs'],
    argTypes: {
        variant: {
            control: 'select',
            options: ['default', 'destructive', 'outline', 'secondary', 'ghost', 'link'],
        },
        size: {
            control: 'select',
            options: ['default', 'sm', 'lg', 'icon'],
        },
    },
    args: {
        variant: 'default',
        size: 'default',
    },
};

export default meta;
type Story = StoryObj<ButtonStoryArgs>;

export const Default: Story = {
    render: (args) => ({
        components: { Button },
        setup: () => ({ args }),
        template: '<Button v-bind="args">Button</Button>',
    }),
};

export const Secondary: Story = {
    args: {
        variant: 'secondary',
    },
    render: (args) => ({
        components: { Button },
        setup: () => ({ args }),
        template: '<Button v-bind="args">Secondary Button</Button>',
    }),
};

export const Destructive: Story = {
    args: {
        variant: 'destructive',
    },
    render: (args) => ({
        components: { Button },
        setup: () => ({ args }),
        template: '<Button v-bind="args">Destructive Button</Button>',
    }),
};

export const Outline: Story = {
    args: {
        variant: 'outline',
    },
    render: (args) => ({
        components: { Button },
        setup: () => ({ args }),
        template: '<Button v-bind="args">Outline Button</Button>',
    }),
};

export const Ghost: Story = {
    args: {
        variant: 'ghost',
    },
    render: (args) => ({
        components: { Button },
        setup: () => ({ args }),
        template: '<Button v-bind="args">Ghost Button</Button>',
    }),
};

export const Link: Story = {
    args: {
        variant: 'link',
    },
    render: (args) => ({
        components: { Button },
        setup: () => ({ args }),
        template: '<Button v-bind="args">Link Button</Button>',
    }),
};
