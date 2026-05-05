<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { showModal } from '@noeldemartin/vue-modals';
import { watchDebounced } from '@vueuse/core';
import Plus from '~icons/lucide/plus';
import Rss from '~icons/lucide/rss';
import { ref } from 'vue';
import Pagination from '@/components/Pagination.vue';
import SearchInput from '@/components/SearchInput.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import CreateFeedModal from './Partials/CreateFeedModal.vue';
import DeleteFeedModal from './Partials/DeleteFeedModal.vue';
import EditFeedModal from './Partials/EditFeedModal.vue';
import FeedTable from './Partials/FeedTable.vue';
import ImportRssModal from './Partials/ImportRssModal.vue';

const props = defineProps<{
    feeds: {
        data: any[];
        links: any[];
    };
    filters: {
        search?: string;
    };
    can?: {
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

const importRss = () => showModal(ImportRssModal);
const createFeed = () => showModal(CreateFeedModal, { canUploadFiles: props.can?.uploadFiles });
const editFeed = (feed: any) =>
    showModal(EditFeedModal, { feed, canUploadFiles: props.can?.uploadFiles });
const deleteFeed = (feed: any) => showModal(DeleteFeedModal, { feed });
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout>
        <div
            class="mx-auto flex h-full w-full max-w-5xl flex-1 flex-col gap-6 overflow-x-auto rounded-none p-4"
        >
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold tracking-tight">Feeds</h2>
                <div class="flex gap-2">
                    <Button variant="outline" @click="importRss">
                        <Rss class="mr-2 h-4 w-4" />
                        Import from RSS
                    </Button>
                    <Button @click="createFeed">
                        <Plus class="mr-2 h-4 w-4" />
                        New Feed
                    </Button>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <SearchInput v-model="search" placeholder="Search feeds..." />
            </div>

            <FeedTable :feeds="feeds.data" @edit="editFeed" @delete="deleteFeed" />

            <Pagination :links="feeds.links" />
        </div>
    </AppLayout>
</template>
