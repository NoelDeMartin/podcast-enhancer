<script setup lang="ts">
import { showModal } from '@noeldemartin/vue-modals';
import { computed } from 'vue';
import AiGenerate from '~icons/carbon/ai-generate';
import Renew from '~icons/carbon/renew';

import FailureModal from '@/components/modals/FailureModal/FailureModal.vue';
import { getBatchStatus } from '@/lib/entries';

const props = defineProps<{
    entry: any;
    showBullet?: boolean;
}>();

const viewFailure = (entry: any) =>
    showModal(FailureModal, {
        title: 'Processing failed',
        description: `For "${entry.name}" episode.`,
        details:
            entry.latest_job_batch?.job_batch?.failed_job_details?.[0]?.exception ??
            'No error details available.',
    });

const status = computed(() => getBatchStatus(props.entry));
const shouldShow = computed(() => status.value !== null || !!props.entry.transcription_path);
</script>

<template>
    <div v-if="shouldShow" class="inline-flex items-center gap-3 text-sm">
        <span v-if="showBullet" class="text-neo-dark/30 hidden sm:inline">•</span>

        <div v-if="status === 'pending'" class="text-muted-foreground flex items-center gap-1.5">
            <Renew class="size-4 animate-spin" />
            Enhancing
        </div>

        <button
            v-else-if="status === 'failed'"
            class="text-red-500 hover:underline"
            @click="viewFailure(entry)"
        >
            Processing failed
        </button>

        <div v-else-if="entry.transcription_path" class="flex items-center gap-1.5">
            <AiGenerate class="size-4" />
            Enhanced
        </div>
    </div>
</template>
