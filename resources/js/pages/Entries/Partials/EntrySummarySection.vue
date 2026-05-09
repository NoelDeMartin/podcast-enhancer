<script setup lang="ts">
import { formatSummary } from '@/lib/entries';

defineProps<{
    entry: any;
}>();
</script>

<template>
    <section v-if="entry.summary" aria-labelledby="entry-summary">
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
                    <h3 id="entry-summary" class="min-w-0 text-lg font-semibold">Summary</h3>
                </span>
                <span
                    class="text-neo-dark/80 inline-flex shrink-0 items-center"
                    title="This content has been generated with AI"
                >
                    <i-carbon-ai-generate aria-hidden="true" class="size-4" />
                    <span class="sr-only"> This content has been generated with AI </span>
                </span>
            </summary>
            <div
                class="text-muted-foreground mt-4 text-sm leading-relaxed [&_a]:text-blue-600 [&_a:hover]:underline [&_ol]:list-decimal [&_ol]:pl-5 [&_p]:mb-4 [&_ul]:list-disc [&_ul]:pl-5"
                v-html="formatSummary(entry.summary)"
            ></div>
        </details>
    </section>
</template>
