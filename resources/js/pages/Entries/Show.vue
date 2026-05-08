<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed, useTemplateRef } from 'vue';

import { useEntryPolling } from '@/composables/useEntryPolling';
import AppLayout from '@/layouts/AppLayout.vue';
import { getBatchStatus } from '@/lib/entries';
import EntryAudioPlayer from '@/pages/Entries/Partials/EntryAudioPlayer.vue';
import EntryChaptersSection from '@/pages/Entries/Partials/EntryChaptersSection.vue';
import EntryHeader from '@/pages/Entries/Partials/EntryHeader.vue';
import EntryShowNotesSection from '@/pages/Entries/Partials/EntryShowNotesSection.vue';
import EntrySummarySection from '@/pages/Entries/Partials/EntrySummarySection.vue';
import EntryTranscriptionSection from '@/pages/Entries/Partials/EntryTranscriptionSection.vue';

const props = defineProps<{
    entry: any;
    can: {
        update: boolean;
        delete: boolean;
        uploadFiles: boolean;
    };
}>();

const isProcessing = computed(() => getBatchStatus(props.entry) === 'pending');
const audioPlayer = useTemplateRef<InstanceType<typeof EntryAudioPlayer>>('audioPlayer');

useEntryPolling(isProcessing);

const seekToTimestamp = (seconds: number) => {
    audioPlayer.value?.seekTo(seconds);
};
</script>

<template>
    <Head :title="entry.name" />

    <AppLayout>
        <div class="mx-auto flex h-full w-full max-w-5xl flex-1 flex-col gap-6 p-4">
            <EntryHeader :entry />

            <EntryAudioPlayer
                v-if="entry.absolute_audio_url"
                ref="audioPlayer"
                :src="entry.absolute_audio_url"
                :open-href="entry.absolute_audio_url"
            />

            <div class="grid gap-6">
                <EntryShowNotesSection :entry />
                <EntrySummarySection :entry />
                <EntryChaptersSection :entry @seek="seekToTimestamp" />
                <EntryTranscriptionSection :entry @seek="seekToTimestamp" />
            </div>
        </div>
    </AppLayout>
</template>
