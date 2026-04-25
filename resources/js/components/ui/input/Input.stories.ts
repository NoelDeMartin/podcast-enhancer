import type { Meta, StoryObj } from '@storybook/vue3-vite';
import type { InputHTMLAttributes } from 'vue';

import { Input } from './index';

type InputStoryArgs = InputHTMLAttributes;

const meta: Meta<InputStoryArgs> = {
    title: 'UI/Input',
    component: Input,
    tags: ['autodocs'],
    argTypes: {
        type: {
            control: 'select',
            options: ['text', 'password', 'email', 'number', 'url'],
        },
        placeholder: { control: 'text' },
        disabled: { control: 'boolean' },
    },
    args: {
        type: 'text',
        placeholder: 'Enter text...',
        disabled: false,
    },
};

export default meta;
type Story = StoryObj<typeof meta>;

export const Default: Story = {
    render: (args) => ({
        components: { Input },
        setup: () => ({ args }),
        template: '<div class="max-w-sm p-6"><Input v-bind="args" /></div>',
    }),
};

export const Password: Story = {
    args: {
        type: 'password',
        placeholder: 'Enter password...',
    },
    render: (args) => ({
        components: { Input },
        setup: () => ({ args }),
        template: '<div class="max-w-sm p-6"><Input v-bind="args" /></div>',
    }),
};

export const Disabled: Story = {
    args: {
        disabled: true,
        placeholder: 'Disabled input',
    },
    render: (args) => ({
        components: { Input },
        setup: () => ({ args }),
        template: '<div class="max-w-sm p-6"><Input v-bind="args" /></div>',
    }),
};
