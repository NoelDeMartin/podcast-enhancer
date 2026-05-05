<script setup lang="ts">
import Clock from '~icons/lucide/clock';

import { Badge } from '@/components/ui/badge';
import { Modal } from '@/components/ui/modal';
import { Separator } from '@/components/ui/separator';
import { formatSummary, formatTimestamp, parsedTranscription } from '@/lib/entries';

defineProps<{
    entry: any;
}>();
</script>

<template>
    <Modal title="Entry Details" :description="entry?.name" class="max-w-2xl">
        <div class="max-h-[60vh] space-y-4 overflow-y-auto">
            <div v-if="entry?.absolute_audio_url" class="border-neo-dark rounded-none border-3 p-4">
                <audio controls class="w-full">
                    <source :src="entry.absolute_audio_url" type="audio/mpeg" />
                    Your browser does not support the audio element.
                </audio>
            </div>

            <div v-if="entry?.summary">
                <h4 class="mb-2 text-sm font-semibold">AI Summary</h4>
                <div
                    class="text-muted-foreground text-sm leading-relaxed [&_a]:text-blue-600 dark:[&_a]:text-blue-400 [&_a:hover]:underline [&_ol]:list-decimal [&_ol]:pl-5 [&_p]:mb-4 [&_ul]:list-disc [&_ul]:pl-5"
                    v-html="formatSummary(entry.summary)"
                ></div>
            </div>

            <Separator v-if="entry?.summary && entry?.original_summary" />

            <div v-if="entry?.original_summary">
                <h4 class="mb-2 text-sm font-semibold">Original Description</h4>
                <div
                    class="text-muted-foreground text-sm leading-relaxed [&_a]:text-blue-600 dark:[&_a]:text-blue-400 [&_a:hover]:underline [&_ol]:list-decimal [&_ol]:pl-5 [&_p]:mb-4 [&_ul]:list-disc [&_ul]:pl-5"
                    v-html="formatSummary(entry.original_summary)"
                ></div>
            </div>

            <Separator
                v-if="(entry?.summary || entry?.original_summary) && entry?.chapters?.length"
            />

            <div v-if="entry?.chapters?.length">
                <h4 class="mb-2 text-sm font-semibold">Chapters</h4>
                <ul class="space-y-2">
                    <li
                        v-for="(chapter, index) in entry.chapters"
                        :key="index"
                        class="flex items-center gap-3"
                    >
                        <Badge variant="secondary" class="shrink-0 text-xs">
                            <Clock class="mr-1 h-3 w-3" />
                            {{ formatTimestamp(chapter.startTime) }}
                        </Badge>
                        <span class="text-sm">{{ chapter.title }}</span>
                    </li>
                </ul>
            </div>

            <Separator
                v-if="
                    (entry?.summary || entry?.original_summary || entry?.chapters?.length) &&
                    parsedTranscription(entry)
                "
            />

            <div v-if="parsedTranscription(entry)">
                <h4 class="mb-2 text-sm font-semibold">Transcription</h4>
                <div class="border-neo-dark rounded-none border-3 p-4">
                    <div
                        v-for="(segment, index) in parsedTranscription(entry)"
                        :key="index"
                        class="mb-2 last:mb-0"
                    >
                        <span class="text-muted-foreground mr-2 text-xs font-medium">
                            [{{ formatTimestamp(segment.start_seconds) }}] {{ segment.speaker }}:
                        </span>
                        <span class="text-sm leading-relaxed">{{ segment.text }}</span>
                    </div>
                </div>
            </div>
        </div>
    </Modal>
</template>
