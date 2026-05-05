<script setup lang="ts">
import { showModal } from '@noeldemartin/vue-modals';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import DeleteUserModal from '@/components/DeleteUserModal.vue';

const deleteAccount = () => showModal(DeleteUserModal);
</script>

<template>
    <div class="space-y-6">
        <Heading
            variant="small"
            title="Delete account"
            description="Delete your account and all of its resources"
        />
        <div
            class="space-y-4 rounded-none border-3 border-neo-dark border-red-100 bg-red-50 p-4 dark:border-3 dark:border-neo-dark dark:border-red-200/10 dark:bg-red-700/10"
        >
            <div class="relative space-y-0.5 text-red-600 dark:text-red-100">
                <p class="font-medium">Warning</p>
                <p class="text-sm">Please proceed with caution, this cannot be undone.</p>
            </div>
            <Button variant="destructive" data-test="delete-user-button" @click="deleteAccount">
                Delete account
            </Button>
        </div>
    </div>
</template>
