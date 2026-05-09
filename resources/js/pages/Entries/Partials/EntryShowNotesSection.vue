<script setup lang="ts">
import { formatSummary } from '@/lib/entries';

defineProps<{
    entry: any;
}>();
</script>

<template>
    <section v-if="entry.original_summary" aria-labelledby="entry-show-notes">
        <details
            class="bg-background group hover:shadow-neo-hard border-3 p-6 transition-all duration-300"
        >
            <summary
                class="flex cursor-pointer list-none items-center justify-between gap-3 [&::-webkit-details-marker]:hidden"
            >
                <i-carbon-chevron-right
                    aria-hidden="true"
                    class="text-neo-dark/70 size-4 shrink-0 transition-transform group-open:rotate-90"
                />
                <h3 id="entry-show-notes" class="flex-1 text-lg font-semibold">Show Notes</h3>
            </summary>
            <div
                class="text-muted-foreground mt-4 text-sm leading-relaxed [&_a]:text-blue-600 [&_a:hover]:underline [&_ol]:list-decimal [&_ol]:pl-5 [&_p]:mb-4 [&_ul]:list-disc [&_ul]:pl-5"
                v-html="formatSummary(entry.original_summary)"
            ></div>
        </details>
    </section>
</template>
