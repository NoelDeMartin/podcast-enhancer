import type { Meta, StoryObj } from '@storybook/vue3-vite';

import { edit as editProfile } from '@/routes/profile';
import { edit as editSecurity } from '@/routes/security';
import { user as stubUser } from '@/testing/stubs/user';

import { setInertiaPage } from '../../../../.storybook/mocks/inertia';
import Profile from './Profile.vue';
import Security from './Security.vue';

type SettingsArgs = {
    tab: 'profile' | 'security';
    mustVerifyEmail?: boolean;
    status?: string;
    emailVerified?: boolean;
};

const meta = {
    title: 'Pages/Settings',
    component: Profile,
    parameters: {
        layout: 'fullscreen',
        inertia: {
            props: {
                auth: {
                    user: stubUser,
                },
            },
        },
    },
    argTypes: {
        tab: {
            control: 'radio',
            options: ['profile', 'security'],
        },
        status: {
            control: 'select',
            options: [undefined, 'verification-link-sent'],
        },
    },
    args: {
        tab: 'profile',
        mustVerifyEmail: true,
        status: undefined,
        emailVerified: false,
    },
    decorators: [
        (story, { args }) => {
            const isProfile = args.tab === 'profile';
            const currentUrl = isProfile ? editProfile() : editSecurity();

            setInertiaPage({
                url: new URL(currentUrl.url, 'http://localhost').pathname,
                props: {
                    auth: {
                        user: {
                            ...stubUser,
                            email_verified_at: args.emailVerified ? '2024-03-12T10:00:00Z' : null,
                        },
                    },
                },
            });

            return story();
        },
    ],
    render: (args) => ({
        components: { Profile, Security },
        setup: () => ({ args }),
        template: `
            <Profile
                v-if="args.tab === 'profile'"
                :must-verify-email="args.mustVerifyEmail ?? true"
                :status="args.status"
            />
            <Security v-else />
        `,
    }),
} satisfies Meta<SettingsArgs>;

export default meta;

type Story = StoryObj<typeof meta>;

export const ProfileTab: Story = {
    args: {
        tab: 'profile',
        mustVerifyEmail: true,
        emailVerified: false,
    },
};

export const SecurityTab: Story = {
    args: {
        tab: 'security',
    },
};

export const VerifiedEmail: Story = {
    args: {
        tab: 'profile',
        mustVerifyEmail: true,
        emailVerified: true,
    },
};

export const VerificationLinkSent: Story = {
    args: {
        tab: 'profile',
        mustVerifyEmail: true,
        emailVerified: false,
        status: 'verification-link-sent',
    },
};
