<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import Clock from '~icons/lucide/clock';
import ExternalLink from '~icons/lucide/external-link';

import { show as showFeedAction } from '@/actions/App/Http/Controllers/FeedController';
import EntryEnhancementActions from '@/components/EntryEnhancementActions.vue';
import EntryEnhancementStatus from '@/components/EntryEnhancementStatus.vue';
import { Badge } from '@/components/ui/badge';
import { useEntryPolling } from '@/composables/useEntryPolling';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatSummary, formatTimestamp, getBatchStatus, parsedTranscription } from '@/lib/entries';

const props = defineProps<{
    entry: any;
    can: {
        update: boolean;
        delete: boolean;
        uploadFiles: boolean;
    };
}>();

const isProcessing = computed(() => getBatchStatus(props.entry) === 'pending');

useEntryPolling(isProcessing);
</script>

<template>
    <Head :title="entry.name" />

    <AppLayout>
        <div class="mx-auto flex h-full w-full max-w-5xl flex-1 flex-col gap-6 p-4">
            <div class="flex flex-col gap-2">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <img
                            v-if="entry.absolute_image_url || entry.feed.absolute_image_url"
                            :src="entry.absolute_image_url || entry.feed.absolute_image_url"
                            alt=""
                            class="border-neo-dark h-16 w-16 border-3 object-cover"
                        />
                        <div>
                            <div class="mb-1">
                                <Link
                                    :href="showFeedAction.url(entry.feed.slug)"
                                    class="text-neo-pink text-sm font-bold tracking-widest uppercase hover:underline"
                                >
                                    {{ entry.feed.title }}
                                </Link>
                            </div>
                            <h2 class="text-2xl font-bold tracking-tight">
                                {{ entry.name }}
                            </h2>
                            <div
                                v-if="entry.published_at"
                                class="text-muted-foreground mt-1 text-sm"
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
                class="bg-background flex items-center justify-between border-3 p-4"
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
                <div v-if="entry.summary" class="bg-background border-3 p-6">
                    <h3 class="mb-4 text-lg font-semibold">AI Summary</h3>
                    <div
                        class="text-muted-foreground text-sm leading-relaxed [&_a]:text-blue-600 dark:[&_a]:text-blue-400 [&_a:hover]:underline [&_ol]:list-decimal [&_ol]:pl-5 [&_p]:mb-4 [&_ul]:list-disc [&_ul]:pl-5"
                        v-html="formatSummary(entry.summary)"
                    ></div>
                </div>

                <div v-if="entry.original_summary" class="bg-background border-3 p-6">
                    <h3 class="mb-4 text-lg font-semibold">Original Description</h3>
                    <div
                        class="text-muted-foreground text-sm leading-relaxed [&_a]:text-blue-600 dark:[&_a]:text-blue-400 [&_a:hover]:underline [&_ol]:list-decimal [&_ol]:pl-5 [&_p]:mb-4 [&_ul]:list-disc [&_ul]:pl-5"
                        v-html="formatSummary(entry.original_summary)"
                    ></div>
                </div>

                <div v-if="entry.chapters?.length" class="bg-background border-3 p-6">
                    <h3 class="mb-4 text-lg font-semibold">Chapters</h3>
                    <ul class="space-y-3">
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

                <div v-if="parsedTranscription(entry)" class="bg-background border-3 p-6">
                    <h3 class="mb-4 text-lg font-semibold">Transcription</h3>
                    <div class="space-y-4">
                        <div v-for="(segment, index) in parsedTranscription(entry)" :key="index">
                            <span class="text-muted-foreground mr-2 text-xs font-medium">
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
