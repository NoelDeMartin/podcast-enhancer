<script setup lang="ts">
import { Head, Link, usePoll, router } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import { showModal } from '@noeldemartin/vue-modals';
import { watchDebounced } from '@vueuse/core';
import { computed, ref, watch } from 'vue';
import Loader2 from '~icons/lucide/loader-2';
import MoreHorizontal from '~icons/lucide/more-horizontal';
import Plus from '~icons/lucide/plus';
import RefreshCw from '~icons/lucide/refresh-cw';
import Rss from '~icons/lucide/rss';

import {
    destroy as destroyEntry,
    show as showEntryAction,
} from '@/actions/App/Http/Controllers/EntryController';
import { show as showFeed } from '@/actions/App/Http/Controllers/FeedController';
import FeedRssController from '@/actions/App/Http/Controllers/FeedRssController';
import { sync as syncFeedAction } from '@/actions/App/Http/Controllers/FeedSyncController';
import EntryEnhancementActions from '@/components/EntryEnhancementActions.vue';
import EntryEnhancementStatus from '@/components/EntryEnhancementStatus.vue';
import Pagination from '@/components/Pagination.vue';
import SearchInput from '@/components/SearchInput.vue';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatDate, getBatchStatus } from '@/lib/entries';

import CreateEntryModal from './Partials/CreateEntryModal.vue';
import EditEntryModal from './Partials/EditEntryModal.vue';
import EntryDetailsModal from './Partials/EntryDetailsModal.vue';
import ImportEpisodesModal from './Partials/ImportEpisodesModal.vue';
import SyncFailureModal from './Partials/SyncFailureModal.vue';

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
const viewEntry = (entry: any) => showModal(EntryDetailsModal, { entry });
const viewSyncFailure = () => showModal(SyncFailureModal, { feed: props.feed });

const deleteEntry = (slug: string) => {
    const entry = props.entries.data.find((e: any) => e.slug === slug);

    if (confirm('Are you sure you want to delete this entry?')) {
        useForm({}).delete(destroyEntry([props.feed, entry.slug]).url);
    }
};

function getFeedSyncStatus(): 'pending' | 'failed' | 'completed' | null {
    const batch = props.feed.latest_job_batch?.job_batch;

    if (!batch) {
        return null;
    }

    if (batch.cancelled_at !== null) {
        return 'failed';
    }

    if (batch.finished_at !== null) {
        return 'completed';
    }

    return 'pending';
}

const hasActiveJobs = computed(
    () =>
        props.entries.data.some((e: any) => getBatchStatus(e) === 'pending') ||
        getFeedSyncStatus() === 'pending',
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
            class="mx-auto flex h-full w-full max-w-5xl flex-1 flex-col gap-6 overflow-x-auto rounded-none p-4"
        >
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <h2 class="text-2xl font-bold tracking-tight">
                        {{ feed.title }}
                    </h2>
                    <a
                        :href="FeedRssController.url(feed.slug)"
                        target="_blank"
                        class="text-orange-500 hover:text-orange-600"
                        title="RSS Feed"
                    >
                        <Rss class="h-5 w-5" />
                    </a>
                </div>

                <div v-if="can.update || can.sync" class="flex items-center gap-2">
                    <div v-if="getFeedSyncStatus() === 'failed'" class="flex items-center gap-1">
                        <button
                            class="text-xs text-red-500 hover:underline"
                            @click="viewSyncFailure"
                        >
                            Sync Failed
                        </button>
                    </div>

                    <Button
                        v-if="feed.rss_url && can.sync"
                        @click="syncFeed"
                        :disabled="isSyncing || getFeedSyncStatus() === 'pending'"
                    >
                        <Loader2
                            v-if="isSyncing || getFeedSyncStatus() === 'pending'"
                            class="mr-2 h-4 w-4 animate-spin"
                        />
                        <RefreshCw v-else class="mr-2 h-4 w-4" />
                        {{ getFeedSyncStatus() === 'pending' ? 'Synchronizing...' : 'Synchronize' }}
                    </Button>
                    <template v-else-if="!feed.rss_url && can.update">
                        <Button @click="addEntry">
                            <Plus class="mr-2 h-4 w-4" />
                            Add Entry
                        </Button>

                        <Button variant="outline" @click="importEpisodes">
                            <Rss class="mr-2 h-4 w-4" />
                            Add from RSS
                        </Button>
                    </template>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <SearchInput v-model="search" placeholder="Search entries..." />
            </div>

            <div class="bg-background border-3">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead class="w-[10%]">Image</TableHead>
                            <TableHead class="w-[33%]">Name</TableHead>
                            <TableHead class="w-[12%]">Published</TableHead>
                            <TableHead class="w-[20%]">Enhancements</TableHead>
                            <TableHead class="w-[10%]">Details</TableHead>
                            <TableHead v-if="can.update || can.delete" class="text-right"
                                >Actions</TableHead
                            >
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <template v-for="entry in entries.data" :key="entry.id">
                            <TableRow>
                                <TableCell>
                                    <img
                                        v-if="entry.absolute_image_url || feed.absolute_image_url"
                                        :src="entry.absolute_image_url || feed.absolute_image_url"
                                        alt=""
                                        class="h-10 w-10 rounded-none object-cover"
                                    />
                                    <div
                                        v-else
                                        class="flex h-10 w-10 items-center justify-center rounded-none bg-gray-100 dark:bg-zinc-800"
                                    >
                                        <Rss class="h-5 w-5 text-gray-400" />
                                    </div>
                                </TableCell>
                                <TableCell class="align-top font-medium">
                                    <Link
                                        :href="showEntryAction.url([props.feed.slug, entry.slug])"
                                        class="hover:underline"
                                    >
                                        {{ entry.name }}
                                    </Link>
                                </TableCell>
                                <TableCell class="text-muted-foreground align-top text-sm">
                                    {{ formatDate(entry.published_at) }}
                                </TableCell>
                                <TableCell class="align-top">
                                    <EntryEnhancementStatus :entry="entry" />
                                </TableCell>
                                <TableCell class="align-top">
                                    <button
                                        type="button"
                                        class="text-blue-600 hover:underline dark:text-blue-400"
                                        data-test="view-details"
                                        @click="viewEntry(entry)"
                                    >
                                        View
                                    </button>
                                </TableCell>
                                <TableCell
                                    v-if="can.update || can.delete"
                                    class="text-right align-top"
                                >
                                    <DropdownMenu>
                                        <DropdownMenuTrigger as-child>
                                            <Button variant="ghost" class="h-8 w-8 p-0">
                                                <span class="sr-only">Open menu</span>
                                                <MoreHorizontal class="h-4 w-4" />
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent align="end">
                                            <template v-if="!feed.rss_url && can.update">
                                                <DropdownMenuItem @click="editEntry(entry)">
                                                    Edit
                                                </DropdownMenuItem>
                                            </template>

                                            <EntryEnhancementActions
                                                v-if="entry.can?.produce || entry.can?.regenerate"
                                                :feed="feed"
                                                :entry="entry"
                                                type="dropdown-items"
                                            />

                                            <template v-if="!feed.rss_url && can.delete">
                                                <DropdownMenuItem
                                                    class="text-red-600"
                                                    @click="deleteEntry(entry.slug)"
                                                >
                                                    Delete
                                                </DropdownMenuItem>
                                            </template>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                </TableCell>
                            </TableRow>
                        </template>
                        <TableRow v-if="entries.data.length === 0">
                            <TableCell
                                :colspan="can.update || can.delete ? 6 : 5"
                                class="h-24 text-center"
                            >
                                No entries yet.
                                <template v-if="can.update">
                                    Click "Add Entry" to create one.
                                </template>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <Pagination :links="entries.links" />
        </div>
    </AppLayout>
</template>
