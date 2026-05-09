<script setup lang="ts">
import { Link, useForm, usePoll } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

import { show } from '@/actions/App/Http/Controllers/FeedController';
import { sync as syncFeedAction } from '@/actions/App/Http/Controllers/FeedSyncController';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { getBatchStatus } from '@/lib/entries';

const props = defineProps<{
    feeds: any[];
}>();

const emit = defineEmits<{
    edit: [feed: any];
    delete: [feed: any];
}>();

const hasActiveJobs = computed(() => props.feeds.some((f) => getBatchStatus(f) === 'pending'));

const { start: startPolling, stop: stopPolling } = usePoll(
    3000,
    { only: ['feeds'] },
    { autoStart: false },
);

watch(hasActiveJobs, (active) => (active ? startPolling() : stopPolling()), {
    immediate: true,
});

const isSyncing = ref<Record<number, boolean>>({});

const isFeedSyncing = (feed: any) => isSyncing.value[feed.id] || getBatchStatus(feed) === 'pending';

const syncFeed = (feed: any) => {
    isSyncing.value[feed.id] = true;
    useForm({}).post(syncFeedAction.url(feed.slug), {
        onFinish: () => {
            isSyncing.value[feed.id] = false;
        },
    });
};

const getSyncStatus = (feed: any) => {
    if (!feed.rss_url) {
        return {
            indicator: 'bg-gray-400',
            label: 'Manual',
        };
    }

    if (!feed.last_synced_at) {
        return {
            indicator: 'bg-gray-400',
            label: 'Never synced',
        };
    }

    const lastSynced = new Date(feed.last_synced_at);
    const now = new Date();
    // Add a 1 minute grace period to account for server/client time drift
    const gracePeriod = 60 * 1000;
    // Default to daily sync (24 hours = 86400 seconds)
    const syncFrequency = 86400;
    const threshold = new Date(lastSynced.getTime() + syncFrequency * 1000 + gracePeriod);

    if (now > threshold) {
        return {
            indicator: 'bg-yellow-400',
            label: 'Needs sync',
        };
    }

    return {
        indicator: 'bg-green-400',
        label: 'Up to date',
    };
};

const formatLastSynced = (date: string) => {
    return new Date(date).toLocaleString(undefined, {
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>

<template>
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
        <Card
            v-for="feed in feeds"
            :key="feed.id"
            class="hover:shadow-neo-hard flex flex-col gap-0 overflow-hidden py-0 transition-all duration-300"
        >
            <div class="group border-neo-dark relative aspect-square overflow-hidden border-b-3">
                <Link :href="show.url(feed.slug)" class="block size-full" tabindex="-1">
                    <img
                        v-if="feed.absolute_image_url"
                        :src="feed.absolute_image_url"
                        alt=""
                        class="size-full object-cover transition-transform duration-300 group-hover:scale-105"
                    />
                    <div v-else class="flex size-full items-center justify-center bg-gray-100">
                        <i-carbon-rss class="size-12 text-gray-400" />
                    </div>
                    <div
                        class="absolute inset-0 bg-black/40 opacity-0 transition-opacity duration-300 group-hover:opacity-100"
                    />
                </Link>

                <div v-if="feed.rss_url" class="absolute top-3 right-3">
                    <TooltipProvider>
                        <Tooltip :delay-duration="0">
                            <TooltipTrigger as-child>
                                <i-carbon-renew
                                    v-if="isFeedSyncing(feed)"
                                    class="size-5 animate-spin text-white drop-shadow-[0_1px_2px_rgba(0,0,0,0.8)]"
                                />
                                <span
                                    v-else
                                    :class="[
                                        'block size-4 rounded-full border-2 border-white shadow-md transition-transform hover:scale-110',
                                        getSyncStatus(feed).indicator,
                                    ]"
                                />
                            </TooltipTrigger>
                            <TooltipContent
                                side="left"
                                class="bg-neo-dark border-neo-dark rounded-none border-2 text-white"
                            >
                                <div class="flex flex-col gap-0.5 p-1 text-xs">
                                    <template v-if="isFeedSyncing(feed)">
                                        <span class="font-bold tracking-wide uppercase"
                                            >Syncing...</span
                                        >
                                    </template>
                                    <template v-else>
                                        <span class="font-bold tracking-wide uppercase">{{
                                            getSyncStatus(feed).label
                                        }}</span>
                                        <span v-if="feed.last_synced_at" class="opacity-90">
                                            Last: {{ formatLastSynced(feed.last_synced_at) }}
                                        </span>
                                    </template>
                                </div>
                            </TooltipContent>
                        </Tooltip>
                    </TooltipProvider>
                </div>
            </div>

            <CardHeader class="flex-1 px-4 py-2">
                <Link :href="show.url(feed.slug)" class="hover:underline">
                    <h3 class="line-clamp-2 text-base leading-tight font-bold">
                        {{ feed.title }}
                    </h3>
                </Link>
            </CardHeader>

            <CardContent class="flex items-center justify-between px-4 pb-2">
                <div
                    class="text-muted-foreground text-right text-xs font-bold tracking-wider uppercase"
                >
                    <span class="text-foreground/70 font-bold">{{ feed.entries_count }}</span>
                    episodes
                </div>

                <DropdownMenu>
                    <DropdownMenuTrigger as-child>
                        <Button variant="ghost" class="-mr-3 size-8 shrink-0 p-0">
                            <span class="sr-only">Open menu for {{ feed.title }}</span>
                            <i-carbon-overflow-menu-vertical class="size-6" />
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end">
                        <DropdownMenuItem
                            v-if="feed.rss_url && feed.can?.sync"
                            class="gap-1.5"
                            :disabled="isFeedSyncing(feed)"
                            @click="syncFeed(feed)"
                        >
                            <i-carbon-renew
                                v-if="isFeedSyncing(feed)"
                                class="size-4 animate-spin"
                            />
                            <i-carbon-renew v-else class="size-4" />
                            {{ isFeedSyncing(feed) ? 'Synchronizing...' : 'Synchronize' }}
                        </DropdownMenuItem>

                        <DropdownMenuItem
                            v-if="!feed?.rss_url"
                            class="gap-1.5"
                            @click="emit('edit', feed)"
                        >
                            <i-carbon-edit class="size-4" />
                            Edit
                        </DropdownMenuItem>
                        <DropdownMenuItem
                            class="gap-1.5 text-red-600"
                            @click="emit('delete', feed)"
                        >
                            <i-carbon-trash-can class="size-4" />
                            Delete
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
            </CardContent>
        </Card>

        <Card
            v-if="feeds.length === 0"
            class="col-span-full flex h-48 items-center justify-center border-dashed"
        >
            <p class="text-muted-foreground">You don't have any podcasts yet, add one!</p>
        </Card>
    </div>
</template>
