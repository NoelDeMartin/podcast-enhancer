import type { Meta, StoryObj } from '@storybook/vue3-vite';
import { ref, watch } from 'vue';

import { Checkbox } from './index';

type CheckboxStoryArgs = {
    modelValue: boolean;
    disabled: boolean;
    label: string;
};

const meta: Meta<CheckboxStoryArgs> = {
    title: 'UI/Checkbox',
    component: Checkbox,
    tags: ['autodocs'],
    argTypes: {
        modelValue: { control: 'boolean' },
        disabled: { control: 'boolean' },
        label: { control: 'text' },
    },
    args: {
        modelValue: false,
        disabled: false,
        label: 'Remember me',
    },
};

export default meta;
type Story = StoryObj<typeof meta>;

export const Default: Story = {
    render: (args) => ({
        components: { Checkbox },
        setup: () => {
            const value = ref(args.modelValue);

            watch(
                () => args.modelValue,
                (next) => {
                    value.value = next;
                },
            );

            return { args, value };
        },
        template: `
            <label class="inline-flex items-center gap-3 p-6 font-mono font-black tracking-wider uppercase">
                <Checkbox
                    :model-value="value"
                    :disabled="args.disabled"
                    @update:modelValue="(v) => (value = v)"
                />
                <span class="text-sm leading-none">{{ args.label }}</span>
            </label>
        `,
    }),
};

export const Checked: Story = {
    args: {
        modelValue: true,
    },
    ...Default,
};

export const Disabled: Story = {
    args: {
        disabled: true,
    },
    ...Default,
};

export const DisabledChecked: Story = {
    args: {
        modelValue: true,
        disabled: true,
    },
    ...Default,
};

