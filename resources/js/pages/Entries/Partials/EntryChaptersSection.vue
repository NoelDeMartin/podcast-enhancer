<script setup lang="ts">
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
        <details
            class="bg-background group hover:shadow-neo-hard border-3 p-6 transition-all duration-300"
        >
            <summary
                class="flex cursor-pointer list-none items-center justify-between gap-3 [&::-webkit-details-marker]:hidden"
            >
                <span class="inline-flex min-w-0 flex-1 items-center gap-2">
                    <i-carbon-chevron-right
                        aria-hidden="true"
                        class="text-neo-dark/70 size-4 shrink-0 transition-transform group-open:rotate-90"
                    />
                    <h3 id="entry-chapters" class="min-w-0 text-lg font-semibold">Chapters</h3>
                </span>
                <span
                    class="text-neo-dark/80 inline-flex shrink-0 items-center"
                    title="This content has been generated with AI"
                >
                    <i-carbon-ai-generate aria-hidden="true" class="size-4" />
                    <span class="sr-only"> This content has been generated with AI </span>
                </span>
            </summary>
            <ul class="mt-4 grid grid-cols-[max-content_1fr] gap-x-3 gap-y-4">
                <li
                    v-for="(chapter, index) in entry.chapters"
                    :key="index"
                    class="col-span-2 grid grid-cols-subgrid"
                    :class="chapter.summary ? 'items-start' : 'items-center'"
                >
                    <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        class="justify-self-stretch px-2 text-xs tabular-nums"
                        :aria-label="`Seek to ${timestamp(chapter.startTime)}`"
                        @click="emit('seek', chapter.startTime)"
                    >
                        <i-carbon-time class="mr-1 size-3" />
                        {{ timestamp(chapter.startTime) }}
                    </Button>
                    <div class="flex min-w-0 flex-col gap-1">
                        <span class="text-sm leading-relaxed font-semibold">{{
                            chapter.title
                        }}</span>
                        <p
                            v-if="chapter.summary"
                            class="text-muted-foreground text-xs leading-relaxed"
                        >
                            {{ chapter.summary }}
                        </p>
                    </div>
                </li>
            </ul>
        </details>
    </section>
</template>
