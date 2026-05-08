<script setup lang="ts">
import AiGenerate from '~icons/carbon/ai-generate';
import ChevronRight from '~icons/carbon/chevron-right';
import Time from '~icons/carbon/time';

import { Button } from '@/components/ui/button';
import { formatEntryTimestamp } from '@/lib/entries';

const props = defineProps<{
    entry: any;
}>();

const emit = defineEmits<{
    seek: [seconds: number];
}>();

const timestamp = (seconds: number) =>
    formatEntryTimestamp(seconds, Number(props.entry.duration) || 0);
</script>

<template>
    <section v-if="entry.chapters?.length" aria-labelledby="entry-chapters">
        <details class="bg-background group border-3 p-6">
            <summary
                class="flex cursor-pointer list-none items-center justify-between gap-3 [&::-webkit-details-marker]:hidden"
            >
                <span class="inline-flex min-w-0 flex-1 items-center gap-2">
                    <ChevronRight
                        aria-hidden="true"
                        class="text-neo-dark/70 size-4 shrink-0 transition-transform group-open:rotate-90"
                    />
                    <h3 id="entry-chapters" class="min-w-0 text-lg font-semibold">Chapters</h3>
                </span>
                <span
                    class="text-neo-dark/80 inline-flex shrink-0 items-center"
                    title="This content has been generated with AI"
                >
                    <AiGenerate aria-hidden="true" class="size-4" />
                    <span class="sr-only"> This content has been generated with AI </span>
                </span>
            </summary>
            <ul class="mt-4 grid grid-cols-[max-content_1fr] gap-x-3 gap-y-3">
                <li
                    v-for="(chapter, index) in entry.chapters"
                    :key="index"
                    class="col-span-2 grid grid-cols-subgrid items-center"
                >
                    <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        class="justify-self-stretch px-2 text-xs tabular-nums"
                        :aria-label="`Seek to ${timestamp(chapter.startTime)}`"
                        @click="emit('seek', chapter.startTime)"
                    >
                        <Time class="mr-1 size-3" />
                        {{ timestamp(chapter.startTime) }}
                    </Button>
                    <span class="text-sm">{{ chapter.title }}</span>
                </li>
            </ul>
        </details>
    </section>
</template>
