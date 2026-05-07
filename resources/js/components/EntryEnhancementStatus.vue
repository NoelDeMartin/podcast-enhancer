<script setup lang="ts">
import { showModal } from '@noeldemartin/vue-modals';
import Renew from '~icons/carbon/renew';

import EntryFailureModal from '@/components/EntryFailureModal.vue';
import { getBatchStatus } from '@/lib/entries';

defineProps<{
    entry: any;
}>();

const viewFailure = (entry: any) => showModal(EntryFailureModal, { entry });
</script>

<template>
    <div class="inline-flex items-center">
        <div
            v-if="getBatchStatus(entry) === 'pending'"
            class="text-muted-foreground flex items-center gap-1 text-sm"
        >
            <Renew class="size-3 animate-spin" />
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

        <span v-else class="text-muted-foreground text-sm"> Missing </span>
    </div>
</template>
