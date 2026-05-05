<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { useTemplateRef } from 'vue';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import { DialogFooter } from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { Modal } from '@/components/ui/modal';

const emit = defineEmits<{
    close: [];
}>();

const passwordInput = useTemplateRef('passwordInput');
</script>

<template>
    <Modal
        title="Are you sure you want to delete your account?"
        description="Once your account is deleted, all of its resources and data will also be permanently deleted. Please enter your password to confirm you would like to permanently delete your account."
    >
        <Form
            v-bind="ProfileController.destroy.form()"
            reset-on-success
            @error="() => passwordInput?.focus()"
            @success="() => emit('close')"
            :options="{
                preserveScroll: true,
            }"
            class="space-y-6"
            v-slot="{ errors, processing, reset, clearErrors }"
        >
            <div class="grid gap-2">
                <Label for="password" class="sr-only">Password</Label>
                <PasswordInput
                    id="password"
                    name="password"
                    ref="passwordInput"
                    placeholder="Password"
                />
                <InputError :message="errors.password" />
            </div>

            <DialogFooter class="gap-2">
                <Button
                    type="button"
                    variant="secondary"
                    @click="
                        () => {
                            clearErrors();
                            reset();
                            emit('close');
                        }
                    "
                >
                    Cancel
                </Button>

                <Button
                    type="submit"
                    variant="destructive"
                    :disabled="processing"
                    data-test="confirm-delete-user-button"
                >
                    Delete account
                </Button>
            </DialogFooter>
        </Form>
    </Modal>
</template>
