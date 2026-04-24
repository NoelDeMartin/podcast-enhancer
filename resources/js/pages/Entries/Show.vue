<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { Clock, ExternalLink } from 'lucide-vue-next';
import { computed } from 'vue';
import { show as showFeedAction } from '@/actions/App/Http/Controllers/FeedController';
import EntryEnhancementActions from '@/components/EntryEnhancementActions.vue';
import EntryEnhancementStatus from '@/components/EntryEnhancementStatus.vue';
import { Badge } from '@/components/ui/badge';
import { useEntryPolling } from '@/composables/useEntryPolling';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatSummary, formatTimestamp, getBatchStatus, parsedTranscription } from '@/lib/entries';
import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    entry: any;
    can: {
        update: boolean;
        delete: boolean;
        uploadFiles: boolean;
    };
}>();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    {
        title: usePage().props.auth.user ? 'Dashboard' : 'Home',
        href: usePage().props.auth.user ? dashboard() : '/',
    },
    {
        title: props.entry.feed.title,
        href: showFeedAction.url(props.entry.feed.slug),
    },
    {
        title: props.entry.name,
        href: '#',
    },
]);

const isProcessing = computed(() => getBatchStatus(props.entry) === 'pending');

useEntryPolling(isProcessing);
</script>

<template>
    <Head :title="entry.name" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto flex h-full w-full max-w-5xl flex-1 flex-col gap-6 p-4">
            <div class="flex flex-col gap-2">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <img
                            v-if="entry.absolute_image_url || entry.feed.absolute_image_url"
                            :src="entry.absolute_image_url || entry.feed.absolute_image_url"
                            alt="Entry image"
                            class="h-16 w-16 rounded object-cover shadow-sm"
                        />
                        <div>
                            <h2 class="text-2xl font-bold tracking-tight">
                                {{ entry.name }}
                            </h2>
                            <div
                                v-if="entry.published_at"
                                class="mt-1 text-sm text-muted-foreground"
                            >
                                Published:
                                {{ new Date(entry.published_at).toLocaleDateString() }}
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col items-end gap-2">
                        <EntryEnhancementStatus :entry="entry" />
                        <EntryEnhancementActions
                            v-if="entry.can?.produce || entry.can?.regenerate"
                            :feed="entry.feed"
                            :entry="entry"
                        />
                    </div>
                </div>
            </div>

            <div
                v-if="entry.absolute_audio_url"
                class="flex items-center justify-between rounded-xl border bg-white p-4 dark:bg-zinc-950"
            >
                <audio controls class="w-full max-w-2xl" :src="entry.absolute_audio_url">
                    Your browser does not support the audio element.
                </audio>
                <a
                    :href="entry.absolute_audio_url"
                    target="_blank"
                    class="ml-4 flex shrink-0 items-center text-sm text-blue-600 hover:underline dark:text-blue-400"
                >
                    <ExternalLink class="mr-1 h-4 w-4" />
                    Open File
                </a>
            </div>

            <div class="grid gap-6">
                <div v-if="entry.summary" class="rounded-xl border bg-white p-6 dark:bg-zinc-950">
                    <h3 class="mb-4 text-lg font-semibold">AI Summary</h3>
                    <div
                        class="text-sm leading-relaxed text-muted-foreground [&_a]:text-blue-600 dark:[&_a]:text-blue-400 [&_a:hover]:underline [&_ol]:list-decimal [&_ol]:pl-5 [&_p]:mb-4 [&_ul]:list-disc [&_ul]:pl-5"
                        v-html="formatSummary(entry.summary)"
                    ></div>
                </div>

                <div
                    v-if="entry.original_summary"
                    class="rounded-xl border bg-white p-6 dark:bg-zinc-950"
                >
                    <h3 class="mb-4 text-lg font-semibold">Original Description</h3>
                    <div
                        class="text-sm leading-relaxed text-muted-foreground [&_a]:text-blue-600 dark:[&_a]:text-blue-400 [&_a:hover]:underline [&_ol]:list-decimal [&_ol]:pl-5 [&_p]:mb-4 [&_ul]:list-disc [&_ul]:pl-5"
                        v-html="formatSummary(entry.original_summary)"
                    ></div>
                </div>

                <div
                    v-if="entry.chapters?.length"
                    class="rounded-xl border bg-white p-6 dark:bg-zinc-950"
                >
                    <h3 class="mb-4 text-lg font-semibold">Chapters</h3>
                    <ul class="space-y-3">
                        <li
                            v-for="(chapter, index) in entry.chapters"
                            :key="index"
                            class="flex items-center gap-3"
                        >
                            <Badge variant="secondary" class="shrink-0 font-mono text-xs">
                                <Clock class="mr-1 h-3 w-3" />
                                {{ formatTimestamp(chapter.startTime) }}
                            </Badge>
                            <span class="text-sm">{{ chapter.title }}</span>
                        </li>
                    </ul>
                </div>

                <div
                    v-if="parsedTranscription(entry)"
                    class="rounded-xl border bg-white p-6 dark:bg-zinc-950"
                >
                    <h3 class="mb-4 text-lg font-semibold">Transcription</h3>
                    <div class="space-y-4">
                        <div v-for="(segment, index) in parsedTranscription(entry)" :key="index">
                            <span class="mr-2 text-xs font-medium text-muted-foreground">
                                [{{ formatTimestamp(segment.start_seconds) }}]
                                {{ segment.speaker }}:
                            </span>
                            <span class="text-sm leading-relaxed">{{ segment.text }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
