import type { Meta, StoryObj } from '@storybook/vue3-vite';

import Heading from './Heading.vue';

type HeadingStoryArgs = {
    title: string;
    description: string;
    variant: 'default' | 'small';
};

const meta: Meta<HeadingStoryArgs> = {
    title: 'Components/Heading',
    component: Heading,
    tags: ['autodocs'],
    argTypes: {
        title: { control: 'text' },
        description: { control: 'text' },
        variant: { control: 'select', options: ['default', 'small'] },
    },
    args: {
        title: 'Heading Title',
        description: 'This is a description for the heading.',
        variant: 'default',
    },
};

export default meta;
type Story = StoryObj<typeof meta>;

export const Default: Story = {
    args: {
        title: 'Project Settings',
        description: 'Manage your project settings and preferences.',
        variant: 'default',
    },
};

export const Small: Story = {
    args: {
        title: 'Section Title',
        description: 'A smaller heading for sections.',
        variant: 'small',
    },
};

export const WithoutDescription: Story = {
    args: {
        title: 'Just a Title',
        description: '',
        variant: 'default',
    },
};
