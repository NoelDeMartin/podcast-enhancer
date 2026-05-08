<script setup lang="ts">
import { stringToSlug } from '@noeldemartin/utils';
import { refDebounced } from '@vueuse/core';
import { computed, ref } from 'vue';
import AiGenerate from '~icons/carbon/ai-generate';
import ChevronRight from '~icons/carbon/chevron-right';
import Time from '~icons/carbon/time';

import SearchInput from '@/components/SearchInput.vue';
import { Button } from '@/components/ui/button';
import { formatEntryTimestamp, parsedTranscription } from '@/lib/entries';

const props = defineProps<{
    entry: any;
}>();

const emit = defineEmits<{
    seek: [seconds: number];
}>();

const search = ref('');
const debouncedSearch = refDebounced(search, 300);
const segments = computed(() => parsedTranscription(props.entry));
const normalizedSegments = computed(() =>
    segments.value?.map((segment) => stringToSlug(segment.text, '')),
);
const filteredSegments = computed(() => {
    const normalizedSearch = stringToSlug(debouncedSearch.value, '');
    const normalizedSegmentsValue = normalizedSegments.value;

    if (!normalizedSearch || !normalizedSegmentsValue || !segments.value) {
        return segments.value;
    }

    return segments.value.filter((_, index) => {
        const normalizedSegment = normalizedSegmentsValue[index];

        return normalizedSegment.includes(normalizedSearch);
    });
});

const timestamp = (seconds: number) =>
    formatEntryTimestamp(seconds, Number(props.entry.duration) || 0);
</script>

<template>
    <section v-if="segments" aria-labelledby="entry-transcription">
        <details
            class="bg-background group hover:shadow-neo-hard border-3 p-6 transition-all duration-300"
        >
            <summary
                class="flex cursor-pointer list-none items-center justify-between gap-3 [&::-webkit-details-marker]:hidden"
            >
                <span class="inline-flex min-w-0 flex-1 items-center gap-2">
                    <ChevronRight
                        aria-hidden="true"
                        class="text-neo-dark/70 size-4 shrink-0 transition-transform group-open:rotate-90"
                    />
                    <h3 id="entry-transcription" class="min-w-0 text-lg font-semibold">
                        Transcription
                    </h3>
                </span>
                <span
                    class="text-neo-dark/80 inline-flex shrink-0 items-center"
                    title="This content has been generated with AI"
                >
                    <AiGenerate aria-hidden="true" class="size-4" />
                    <span class="sr-only"> This content has been generated with AI </span>
                </span>
            </summary>
            <div class="mt-4 flex flex-col gap-4">
                <SearchInput
                    v-model="search"
                    placeholder="Search transcription..."
                    aria-label="Search transcription"
                />
                <div class="grid grid-cols-[max-content_1fr] gap-x-3 gap-y-4">
                    <div
                        v-for="segment in filteredSegments"
                        :key="segment.start_seconds"
                        class="col-span-2 grid grid-cols-subgrid items-start"
                    >
                        <Button
                            type="button"
                            variant="outline"
                            size="sm"
                            class="justify-self-stretch px-2 text-xs tabular-nums"
                            :aria-label="`Seek to ${timestamp(segment.start_seconds)}`"
                            @click="emit('seek', segment.start_seconds)"
                        >
                            <Time class="mr-1 size-3" />
                            {{ timestamp(segment.start_seconds) }}
                        </Button>
                        <span class="min-w-0 text-sm leading-relaxed">
                            {{ segment.text }}
                        </span>
                    </div>
                </div>
            </div>
        </details>
    </section>
</template>
