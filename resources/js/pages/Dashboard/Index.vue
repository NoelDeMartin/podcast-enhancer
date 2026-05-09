<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { showModal } from '@noeldemartin/vue-modals';
import { watchDebounced } from '@vueuse/core';
import { ref } from 'vue';

import SearchInput from '@/components/SearchInput.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';

import DeleteFeedModal from './Partials/DeleteFeedModal.vue';
import EditFeedModal from './Partials/EditFeedModal.vue';
import FeedGrid from './Partials/FeedGrid.vue';
import NewFeedModal from './Partials/NewFeedModal.vue';

const props = defineProps<{
    feeds: any[];
    filters: {
        search?: string;
    };
    can?: {
        createManual: boolean;
        uploadFiles: boolean;
    };
}>();

const search = ref(props.filters.search || '');

watchDebounced(
    search,
    (value) => {
        router.get(dashboard(), { search: value }, { preserveState: true, replace: true });
    },
    { debounce: 300 },
);

const createFeed = () =>
    showModal(NewFeedModal, {
        canCreateManual: props.can?.createManual,
        canUploadFiles: props.can?.uploadFiles,
    });
const editFeed = (feed: any) => {
    if (feed.rss_url) {
        return;
    }

    showModal(EditFeedModal, { feed, canUploadFiles: props.can?.uploadFiles });
};
const deleteFeed = (feed: any) => showModal(DeleteFeedModal, { feed });
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout>
        <div
            class="mx-auto flex size-full max-w-7xl flex-1 flex-col gap-6 overflow-x-auto rounded-none p-4 sm:p-6"
        >
            <h2 class="sr-only">Podcasts</h2>
            <div class="flex flex-col items-center justify-between gap-4 sm:flex-row">
                <div class="flex w-full items-center">
                    <SearchInput v-model="search" placeholder="Search podcasts..." class="w-full" />
                </div>
                <div class="flex w-full items-center sm:w-auto">
                    <Button @click="createFeed" class="w-full sm:w-auto">
                        <i-carbon-add class="mr-2 size-4" />
                        New Podcast
                    </Button>
                </div>
            </div>

            <FeedGrid :feeds="feeds" @edit="editFeed" @delete="deleteFeed" />
        </div>
    </AppLayout>
</template>
