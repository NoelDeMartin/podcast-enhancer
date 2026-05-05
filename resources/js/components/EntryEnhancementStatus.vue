<script setup lang="ts">
import Loader2 from '~icons/lucide/loader-2';
import { showModal } from '@noeldemartin/vue-modals';
import { getBatchStatus } from '@/lib/entries';
import EntryFailureModal from '@/components/EntryFailureModal.vue';

defineProps<{
    entry: any;
}>();

const viewFailure = (entry: any) => showModal(EntryFailureModal, { entry });
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
            @click="viewFailure(entry)"
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
    </div>
</template>
