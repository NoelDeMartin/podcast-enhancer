<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';

import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { login } from '@/routes';
import { email } from '@/routes/password';

defineProps<{
    status?: string;
}>();
</script>

<template>
    <AuthLayout
        title="Forgot password"
        description="Enter your email to receive a password reset link"
    >
        <Head title="Forgot password" />

        <div v-if="status" class="mb-4 text-center text-sm font-medium text-green-600">
            {{ status }}
        </div>

        <div class="grid gap-8">
            <Form v-bind="email.form()" v-slot="{ errors, processing }" class="flex flex-col gap-8">
                <div class="grid gap-3">
                    <Label for="email">Email address</Label>
                    <Input
                        id="email"
                        type="email"
                        name="email"
                        autocomplete="off"
                        autofocus
                        placeholder="email@example.com"
                    />
                    <InputError :message="errors.email" />
                </div>

                <div class="flex items-center justify-start">
                    <Button
                        class="border-neo-dark bg-neo-pink w-full border-3 font-black tracking-wider text-white uppercase"
                        :disabled="processing"
                        data-test="email-password-reset-link-button"
                    >
                        <Spinner v-if="processing" />
                        Email Password Reset Link
                    </Button>
                </div>
            </Form>

            <div class="text-muted-foreground space-x-1 text-center text-sm">
                <span>Or, return to</span>
                <TextLink :href="login()">log in</TextLink>
            </div>
        </div>
    </AuthLayout>
</template>
