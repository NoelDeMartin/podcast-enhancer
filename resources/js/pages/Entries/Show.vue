<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import DOMPurify from 'dompurify';
import linkifyHtml from 'linkify-html';
import { Clock, ExternalLink } from 'lucide-vue-next';
import { file as getEntryFile } from '@/actions/App/Http/Controllers/EntryController';
import { show as showFeedAction } from '@/actions/App/Http/Controllers/FeedController';
import { Badge } from '@/components/ui/badge';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    entry: any;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
    },
    {
        title: props.entry.feed.title,
        href: showFeedAction.url(props.entry.feed.id),
    },
    {
        title: props.entry.name,
        href: '#',
    },
];

function formatTimestamp(seconds: number): string {
    const h = Math.floor(seconds / 3600);
    const m = Math.floor((seconds % 3600) / 60);
    const s = Math.floor(seconds % 60);

    return h > 0
        ? `${h}:${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`
        : `${m}:${String(s).padStart(2, '0')}`;
}

function formatSummary(summary: string | null | undefined): string {
    if (!summary) {
        return '';
    }

    const linkedSummary = linkifyHtml(summary, {
        defaultProtocol: 'https',
        target: '_blank',
        rel: 'noopener noreferrer',
    });

    return DOMPurify.sanitize(linkedSummary, { ADD_ATTR: ['target'] });
}

function parsedTranscription(entry: any): any[] | null {
    if (!entry?.transcription) {
        return null;
    }

    try {
        return JSON.parse(entry.transcription);
    } catch {
        return null;
    }
}
</script>

<template>
    <Head :title="entry.name" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="mx-auto flex h-full w-full max-w-5xl flex-1 flex-col gap-6 p-4"
        >
            <div class="flex flex-col gap-2">
                <div class="flex items-center gap-4">
                    <img
                        v-if="entry.image_url || entry.feed.image_url"
                        :src="
                            (
                                entry.image_url || entry.feed.image_url
                            ).startsWith('http')
                                ? entry.image_url || entry.feed.image_url
                                : `/storage/${entry.image_url || entry.feed.image_url}`
                        "
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
                            {{
                                new Date(
                                    entry.published_at,
                                ).toLocaleDateString()
                            }}
                        </div>
                    </div>
                </div>
            </div>

            <div
                v-if="entry.audio_url"
                class="flex items-center justify-between rounded-xl border bg-white p-4 dark:bg-zinc-950"
            >
                <audio
                    controls
                    class="w-full max-w-2xl"
                    :src="
                        entry.audio_url.startsWith('http')
                            ? entry.audio_url
                            : getEntryFile.url(entry.id)
                    "
                >
                    Your browser does not support the audio element.
                </audio>
                <a
                    :href="
                        entry.audio_url.startsWith('http')
                            ? entry.audio_url
                            : getEntryFile.url(entry.id)
                    "
                    target="_blank"
                    class="ml-4 flex shrink-0 items-center text-sm text-blue-600 hover:underline dark:text-blue-400"
                >
                    <ExternalLink class="mr-1 h-4 w-4" />
                    Open File
                </a>
            </div>

            <div class="grid gap-6">
                <!-- Failure details if the job failed -->
                <div
                    v-if="
                        entry.latest_job_batch?.job_batch?.cancelled_at !== null
                    "
                    class="rounded-xl border bg-red-50 p-4 dark:bg-red-950/20"
                >
                    <h3
                        class="mb-2 font-semibold text-red-800 dark:text-red-300"
                    >
                        Processing Failed
                    </h3>
                    <pre
                        class="text-xs leading-relaxed whitespace-pre-wrap text-red-800 dark:text-red-300"
                        >{{
                            entry.latest_job_batch?.job_batch
                                ?.failedJobDetails?.[0]?.exception ??
                            'No exception details available.'
                        }}</pre
                    >
                </div>

                <div
                    v-if="entry.summary"
                    class="rounded-xl border bg-white p-6 dark:bg-zinc-950"
                >
                    <h3 class="mb-4 text-lg font-semibold">Summary</h3>
                    <div
                        class="text-sm leading-relaxed text-muted-foreground [&_a]:text-blue-600 dark:[&_a]:text-blue-400 [&_a:hover]:underline [&_ol]:list-decimal [&_ol]:pl-5 [&_p]:mb-4 [&_ul]:list-disc [&_ul]:pl-5"
                        v-html="formatSummary(entry.summary)"
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
                            <Badge
                                variant="secondary"
                                class="shrink-0 font-mono text-xs"
                            >
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
                        <div
                            v-for="(segment, index) in parsedTranscription(
                                entry,
                            )"
                            :key="index"
                        >
                            <span
                                class="mr-2 text-xs font-medium text-muted-foreground"
                            >
                                [{{ formatTimestamp(segment.start_seconds) }}]
                                {{ segment.speaker }}:
                            </span>
                            <span class="text-sm leading-relaxed">{{
                                segment.text
                            }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
