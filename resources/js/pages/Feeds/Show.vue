<script setup lang="ts">
import { Head, router, usePoll } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import { showModal } from '@noeldemartin/vue-modals';
import { watchDebounced } from '@vueuse/core';
import { computed, ref, watch } from 'vue';

import { destroy as destroyEntry } from '@/actions/App/Http/Controllers/EntryController';
import { show as showFeed } from '@/actions/App/Http/Controllers/FeedController';
import FeedRssController from '@/actions/App/Http/Controllers/FeedRssController';
import { sync as syncFeedAction } from '@/actions/App/Http/Controllers/FeedSyncController';
import FailureModal from '@/components/modals/FailureModal/FailureModal.vue';
import SearchInput from '@/components/SearchInput.vue';
import ServerPagination from '@/components/ServerPagination.vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { getBatchStatus } from '@/lib/entries';

import CreateEntryModal from './Partials/CreateEntryModal.vue';
import EditEntryModal from './Partials/EditEntryModal.vue';
import FeedEntryItem from './Partials/FeedEntryItem.vue';
import ImportEpisodesModal from './Partials/ImportEpisodesModal.vue';

const props = defineProps<{
    feed: any;
    entries: {
        data: any[];
        links: any[];
    };
    filters: {
        search?: string;
    };
    can: {
        update: boolean;
        delete: boolean;
        sync: boolean;
        uploadFiles: boolean;
    };
}>();

const search = ref(props.filters.search || '');

watchDebounced(
    search,
    (value) => {
        router.get(
            showFeed(props.feed.slug).url,
            { search: value },
            { preserveState: true, replace: true },
        );
    },
    { debounce: 300 },
);

const addEntry = () =>
    showModal(CreateEntryModal, { feed: props.feed, canUploadFiles: props.can.uploadFiles });
const importEpisodes = () => showModal(ImportEpisodesModal, { feed: props.feed });
const editEntry = (entry: any) =>
    showModal(EditEntryModal, { feed: props.feed, entry, canUploadFiles: props.can.uploadFiles });
const viewSyncFailure = () =>
    showModal(FailureModal, {
        title: 'Synchronization failed',
        description: props.feed.title,
        details:
            props.feed.latest_job_batch?.job_batch?.failed_job_details?.[0]?.exception ??
            'No error details available.',
    });

const deleteEntry = (slug: string) => {
    const entry = props.entries.data.find((e: any) => e.slug === slug);

    if (confirm('Are you sure you want to delete this episode?')) {
        useForm({}).delete(destroyEntry([props.feed, entry.slug]).url);
    }
};

const hasActiveJobs = computed(
    () =>
        props.entries.data.some((e: any) => getBatchStatus(e) === 'pending') ||
        getBatchStatus(props.feed) === 'pending',
);

const { start: startPolling, stop: stopPolling } = usePoll(
    3000,
    { only: ['feed', 'entries'] },
    { autoStart: false },
);

watch(hasActiveJobs, (active) => (active ? startPolling() : stopPolling()), {
    immediate: true,
});

const isSyncing = ref(false);
const syncFeed = () => {
    isSyncing.value = true;
    useForm({}).post(syncFeedAction.url(props.feed.slug), {
        onFinish: () => {
            isSyncing.value = false;
        },
    });
};
</script>

<template>
    <Head :title="feed.title" />

    <AppLayout>
        <div
            class="mx-auto flex size-full max-w-5xl flex-1 flex-col gap-4 overflow-x-hidden rounded-none p-3 sm:gap-6 sm:p-4"
        >
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex min-w-0 items-center gap-3">
                    <h2 class="min-w-0 truncate text-xl font-bold tracking-tight sm:text-2xl">
                        {{ feed.title }}
                    </h2>
                    <a
                        :href="FeedRssController.url(feed.slug)"
                        target="_blank"
                        class="text-orange-500 hover:text-orange-600"
                        title="RSS Podcast Feed"
                    >
                        <i-carbon-rss class="size-5" />
                    </a>
                </div>

                <div
                    v-if="can.update || can.sync"
                    class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row sm:items-center sm:justify-end"
                >
                    <Button
                        v-if="feed.rss_url && can.sync"
                        @click="syncFeed"
                        :disabled="isSyncing || getBatchStatus(feed) === 'pending'"
                        class="w-full sm:w-auto"
                    >
                        <i-carbon-renew
                            v-if="isSyncing || getBatchStatus(feed) === 'pending'"
                            class="mr-2 size-4 animate-spin"
                        />
                        <i-carbon-renew v-else class="mr-2 size-4" />
                        {{
                            getBatchStatus(feed) === 'pending' ? 'Synchronizing...' : 'Synchronize'
                        }}
                    </Button>
                    <template v-else-if="!feed.rss_url && can.update">
                        <Button @click="addEntry" class="w-full sm:w-auto">
                            <i-carbon-add class="mr-2 size-4" />
                            Add Episode
                        </Button>

                        <Button variant="outline" @click="importEpisodes" class="w-full sm:w-auto">
                            <i-carbon-rss class="mr-2 size-4" />
                            Add from RSS
                        </Button>
                    </template>
                </div>
            </div>

            <Alert v-if="getBatchStatus(feed) === 'failed'" variant="destructive">
                <i-carbon-warning-alt />
                <AlertTitle>Synchronization Failed</AlertTitle>
                <AlertDescription>
                    <p>The most recent sync failed. View the error details to investigate.</p>
                    <Button
                        size="sm"
                        variant="secondary"
                        class="mt-3 w-full sm:mt-1 sm:w-auto"
                        @click="viewSyncFailure"
                    >
                        View Details
                    </Button>
                </AlertDescription>
            </Alert>

            <div class="flex w-full items-center gap-4">
                <SearchInput v-model="search" placeholder="Search episodes..." />
            </div>

            <div class="bg-background">
                <ul v-if="entries.data.length > 0" class="flex flex-col gap-8">
                    <FeedEntryItem
                        v-for="entry in entries.data"
                        :key="entry.id"
                        :feed="feed"
                        :entry="entry"
                        :can="{ update: can.update, delete: can.delete }"
                        @edit="editEntry"
                        @delete="deleteEntry"
                    />
                </ul>

                <Card v-else class="flex h-48 items-center justify-center border-dashed">
                    <p class="text-muted-foreground">No episodes yet.</p>
                </Card>
            </div>

            <ServerPagination :links="entries.links" label="Episode Pages" />
        </div>
    </AppLayout>
</template>
