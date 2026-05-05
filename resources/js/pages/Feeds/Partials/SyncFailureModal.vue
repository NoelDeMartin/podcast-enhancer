<script setup lang="ts">
import { Modal } from '@/components/ui/modal';

defineProps<{
    feed: any;
}>();
</script>

<template>
    <Modal title="Synchronization Failed" :description="feed.title" class="max-w-2xl">
        <div
            class="border-neo-dark max-h-[60vh] overflow-y-auto rounded-none border-3 bg-red-50 p-4 dark:bg-red-950/20"
        >
            <pre
                class="text-xs leading-relaxed whitespace-pre-wrap text-red-800 dark:text-red-300"
                >{{
                    feed.latest_job_batch?.job_batch?.failed_job_details?.[0]?.exception ??
                    'No exception details available.'
                }}</pre
            >
        </div>
    </Modal>
</template>
