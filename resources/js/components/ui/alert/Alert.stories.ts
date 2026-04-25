import type { Meta, StoryObj } from '@storybook/vue3-vite';

import { Alert, AlertDescription, AlertTitle } from './index';

type AlertStoryArgs = {
    variant: 'default' | 'destructive';
};

const meta: Meta<AlertStoryArgs> = {
    title: 'UI/Alert',
    component: Alert,
    tags: ['autodocs'],
    argTypes: {
        variant: {
            control: 'select',
            options: ['default', 'destructive'],
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
        components: { Alert, AlertTitle, AlertDescription },
        setup: () => ({ args }),
        template: `
          <div class="p-6">
            <Alert v-bind="args">
              <AlertTitle>Heads up!</AlertTitle>
              <AlertDescription>
                You can add components to your app using the cli.
              </AlertDescription>
            </Alert>
          </div>
        `,
    }),
};

export const Destructive: Story = {
    args: {
        variant: 'destructive',
    },
    render: (args) => ({
        components: { Alert, AlertTitle, AlertDescription },
        setup: () => ({ args }),
        template: `
          <div class="p-6">
            <Alert v-bind="args">
              <AlertTitle>Error</AlertTitle>
              <AlertDescription>
                Your session has expired. Please log in again.
              </AlertDescription>
            </Alert>
          </div>
        `,
    }),
};
