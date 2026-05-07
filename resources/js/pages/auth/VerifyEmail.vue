<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';

import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { logout } from '@/routes';
import { send } from '@/routes/verification';

defineProps<{
    status?: string;
}>();
</script>

<template>
    <AuthLayout
        title="Verify email"
        description="Please verify your email address by clicking on the link we just emailed to you."
        :logo-url="null"
    >
        <Head title="Email verification" />

        <div
            v-if="status === 'verification-link-sent'"
            class="mb-4 text-center text-sm font-medium text-green-600"
        >
            A new verification link has been sent to the email address you provided during
            registration.
        </div>

        <Form v-bind="send.form()" class="grid gap-8 text-center" v-slot="{ processing }">
            <Button :disabled="processing" class="font-black tracking-wider uppercase">
                <Spinner v-if="processing" />
                Resend Verification Email
            </Button>

            <TextLink :href="logout()" as="button" method="post" class="mx-auto block text-sm">
                Log out
            </TextLink>
        </Form>
    </AuthLayout>
</template>
