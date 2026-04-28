<script setup lang="ts">
import Loader2 from '~icons/lucide/loader-2';
import { ref } from 'vue';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { getBatchStatus } from '@/lib/entries';

defineProps<{
    entry: any;
}>();

const viewingFailure = ref(false);
</script>

<template>
    <div class="inline-flex items-center">
        <div
            v-if="getBatchStatus(entry) === 'pending'"
            class="flex items-center gap-1 text-sm text-muted-foreground"
        >
            <Loader2 class="h-3 w-3 animate-spin" />
            Pending
        </div>

        <button
            v-else-if="getBatchStatus(entry) === 'failed'"
            class="text-sm text-red-500 hover:underline"
            @click="viewingFailure = true"
        >
            Failed
        </button>

        <span
            v-else-if="entry.transcription_path"
            class="text-sm text-green-600 dark:text-green-400"
        >
            Available
        </span>

        <span v-else class="text-sm text-muted-foreground"> Missing </span>

        <Dialog :open="viewingFailure" @update:open="viewingFailure = false">
            <DialogContent class="max-w-2xl">
                <DialogHeader>
                    <DialogTitle>Processing Failed</DialogTitle>
                    <DialogDescription>
                        {{ entry.name }}
                    </DialogDescription>
                </DialogHeader>
                <div
                    class="max-h-[60vh] overflow-y-auto rounded-none border-3 border-neo-dark bg-red-50 p-4 dark:bg-red-950/20"
                >
                    <pre
                        class="text-xs leading-relaxed whitespace-pre-wrap text-red-800 dark:text-red-300"
                        >{{
                            entry.latest_job_batch?.job_batch?.failed_job_details?.[0]?.exception ??
                            'No exception details available.'
                        }}</pre
                    >
                </div>
            </DialogContent>
        </Dialog>
    </div>
</template>
